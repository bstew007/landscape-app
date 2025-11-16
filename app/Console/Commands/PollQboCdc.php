<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\QboToken;
use App\Models\Contact;

class PollQboCdc extends Command
{
    protected $signature = 'qbo:cdc:customers {--since=}';
    protected $description = 'Poll QuickBooks CDC for Customer changes and update local contacts';

    protected function baseUrl(string $realmId): string
    {
        $env = config('qbo.environment');
        $host = $env === 'production' ? 'quickbooks.api.intuit.com' : 'sandbox-quickbooks.api.intuit.com';
        return "https://{$host}/v3/company/{$realmId}";
    }

    protected function authHeaders(): array
    {
        $token = QboToken::latest('updated_at')->first();
        return ['Authorization' => 'Bearer '.$token->access_token, 'Accept' => 'application/json'];
    }

    protected function refreshTokenIfNeeded(): void
    {
        $token = QboToken::latest('updated_at')->first();
        if (!$token) return;
        if (!$token->expires_at || now()->diffInSeconds($token->expires_at, false) > 60) return;
        $conf = config('qbo');
        $res = Http::asForm()->withBasicAuth($conf['client_id'], $conf['client_secret'])
            ->post('https://oauth.platform.intuit.com/oauth2/v1/tokens/bearer', [
                'grant_type' => 'refresh_token',
                'refresh_token' => $token->refresh_token,
            ]);
        if ($res->ok()) {
            $data = $res->json();
            $token->access_token = $data['access_token'] ?? $token->access_token;
            if (!empty($data['refresh_token'])) $token->refresh_token = $data['refresh_token'];
            if (!empty($data['expires_in'])) $token->expires_at = now()->addSeconds($data['expires_in']);
            $token->save();
        }
    }

    protected function mapNames(array $c): array
    {
        $display = trim($c['DisplayName'] ?? '');
        $company = trim($c['CompanyName'] ?? '');
        $first = trim($c['GivenName'] ?? '');
        $last = trim($c['FamilyName'] ?? '');
        if ($company !== '') {
            $outFirst = $first !== '' ? $first : ($display !== '' ? $display : 'Customer');
            $outLast = $last !== '' ? $last : ((string) ($c['Id'] ?? ''));
            return ['first' => $outFirst, 'last' => $outLast, 'company' => $company];
        }
        if ($first !== '' || $last !== '') return ['first' => $first ?: 'Customer', 'last' => $last ?: ((string) ($c['Id'] ?? '')), 'company' => null];
        if ($display !== '') {
            if (str_contains($display, ',')) { [$l,$f] = array_map('trim', explode(',', $display, 2)); return ['first'=>$f?:'Customer','last'=>$l?:((string) ($c['Id'] ?? '')),'company'=>null]; }
            $parts = preg_split('/\s+/', $display); if (count($parts)>=2){ $f=array_shift($parts); $l=implode(' ', $parts); return ['first'=>$f?:'Customer','last'=>$l?:((string) ($c['Id'] ?? '')),'company'=>null]; }
            return ['first'=>$display,'last'=>((string) ($c['Id'] ?? '')),'company'=>null];
        }
        return ['first'=>'Customer','last'=>(string)($c['Id']??''),'company'=>null];
    }

    public function handle(): int
    {
        $token = QboToken::latest('updated_at')->first();
        if (!$token) {
            $this->warn('QBO not connected');
            return self::SUCCESS;
        }

        $realm = $token->realm_id;
        $sinceOpt = $this->option('since');
        $since = $sinceOpt ? \Carbon\Carbon::parse($sinceOpt) : Cache::get('qbo.cdc.customer_since');
        if (!$since) { $since = now()->subHours(24); }

        $url = $this->baseUrl($realm).'/cdc';
        $params = [
            'entities' => 'Customer',
            'changedSince' => $since->toIso8601String(),
            'minorversion' => 65,
        ];

        $res = Http::withHeaders($this->authHeaders())->get($url, $params);
        if ($res->status() === 401 || str_contains($res->body(), 'Token expired')) {
            $this->refreshTokenIfNeeded();
            $res = Http::withHeaders($this->authHeaders())->get($url, $params);
        }
        if (!$res->ok()) {
            Log::error('QBO CDC request failed', ['status'=>$res->status(), 'body'=>$res->body()]);
            return self::FAILURE;
        }

        $data = $res->json();
        $cdcResponses = $data['CDCResponse'] ?? [];
        $count = 0;
        $maxUpdated = $since;

        foreach ($cdcResponses as $cdc) {
            foreach (($cdc['QueryResponse'] ?? []) as $qr) {
                $customers = $qr['Customer'] ?? [];
                foreach ($customers as $c) {
                    try {
                        $addr = $c['BillAddr'] ?? [];
                        $email = $c['PrimaryEmailAddr']['Address'] ?? null;
                        $phone = $c['PrimaryPhone']['FreeFormNumber'] ?? null;
                        $mobile = $c['Mobile']['FreeFormNumber'] ?? null;
                        $balance = isset($c['BalanceWithJobs']) ? (float) $c['BalanceWithJobs'] : (isset($c['Balance']) ? (float) $c['Balance'] : null);
                        $names = $this->mapNames($c);
                        if (empty($names['first'])) $names['first'] = 'Customer';
                        if (!array_key_exists('last', $names) || $names['last'] === null) $names['last'] = (string) ($c['Id'] ?? '');

                        $contact = Contact::firstOrNew(['qbo_customer_id' => $c['Id']]);
                        $contact->fill([
                            'first_name' => $names['first'],
                            'last_name' => $names['last'],
                            'company_name' => $names['company'] ?? null,
                            'contact_type' => $contact->contact_type ?: 'client',
                            'email' => $email,
                            'phone' => $phone,
                            'mobile' => $mobile,
                            'address' => $addr['Line1'] ?? null,
                            'city' => $addr['City'] ?? null,
                            'state' => $addr['CountrySubDivisionCode'] ?? null,
                            'postal_code' => $addr['PostalCode'] ?? null,
                        ]);
                        $contact->qbo_balance = $balance;
                        $contact->qbo_sync_token = $c['SyncToken'] ?? $contact->qbo_sync_token;
                        $contact->qbo_last_synced_at = now();
                        $contact->save();
                        $count++;

                        $lut = $c['MetaData']['LastUpdatedTime'] ?? null;
                        if ($lut) {
                            $t = \Carbon\Carbon::parse($lut);
                            if ($t->gt($maxUpdated)) $maxUpdated = $t;
                        }
                    } catch (\Throwable $e) {
                        Log::error('QBO CDC customer sync failed', ['id' => $c['Id'] ?? null, 'error' => $e->getMessage()]);
                    }
                }
            }
        }

        // Advance cursor to just after maxUpdated to avoid reprocessing
        $nextSince = $maxUpdated->copy()->addSecond();
        Cache::put('qbo.cdc.customer_since', $nextSince, now()->addDays(14));

        $this->info("QBO CDC processed {$count} customers; next since: ".$nextSince->toIso8601String());
        Log::info('QBO CDC complete', ['count' => $count, 'next_since' => $nextSince->toIso8601String()]);

        return self::SUCCESS;
    }
}
