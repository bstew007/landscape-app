<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\QboToken;
use App\Services\QboCustomerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ContactQboSyncController extends Controller
{
    public function sync(Contact $client, QboCustomerService $svc)
    {
        try {
            $svc->upsert($client);
            return back()->with('success', 'Synced to QuickBooks');
        } catch (\Throwable $e) {
            return back()->with('error', 'QBO sync failed: '.$e->getMessage());
        }
    }

    // Refresh local contact from QBO (inbound)
    public function refresh(Contact $client)
    {
        try {
            if (!$client->qbo_customer_id) {
                return back()->with('error', 'This contact is not linked to QBO.');
            }
            $token = QboToken::latest('updated_at')->first();
            if (!$token) return back()->with('error', 'QBO not connected.');

            $realmId = $token->realm_id;
            $url = $this->baseUrl($realmId).'/customer/'.$client->qbo_customer_id;
            $res = Http::withHeaders($this->authHeaders())->get($url, ['minorversion' => 65]);
            if ($res->status() === 401 || str_contains($res->body(), 'Token expired')) {
                $this->refreshTokenIfNeeded();
                $res = Http::withHeaders($this->authHeaders())->get($url, ['minorversion' => 65]);
            }
            if (!$res->ok()) {
                return back()->with('error', 'QBO fetch failed: '.$res->body());
            }
            $c = $res->json()['Customer'] ?? [];
            if (!$c) return back()->with('error', 'QBO customer not found.');

            $addr = $c['BillAddr'] ?? [];
            $email = $c['PrimaryEmailAddr']['Address'] ?? null;
            $phone = $c['PrimaryPhone']['FreeFormNumber'] ?? null;
            $mobile = $c['Mobile']['FreeFormNumber'] ?? null;
            $names = $this->mapNames($c);
            if (empty($names['first'])) $names['first'] = 'Customer';
            if (!array_key_exists('last', $names) || $names['last'] === null) $names['last'] = (string) ($c['Id'] ?? '');

            $client->fill([
                'first_name' => $names['first'],
                'last_name' => $names['last'],
                'company_name' => $names['company'] ?? null,
                'email' => $email,
                'phone' => $phone,
                'mobile' => $mobile,
                'address' => $addr['Line1'] ?? null,
                'city' => $addr['City'] ?? null,
                'state' => $addr['CountrySubDivisionCode'] ?? null,
                'postal_code' => $addr['PostalCode'] ?? null,
            ]);
            $client->qbo_sync_token = $c['SyncToken'] ?? $client->qbo_sync_token;
            $client->qbo_last_synced_at = now();
            $client->save();

            return back()->with('success', 'Refreshed from QuickBooks.');
        } catch (\Throwable $e) {
            return back()->with('error', 'QBO refresh failed: '.$e->getMessage());
        }
    }

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
}
