<?php

namespace App\Http\Controllers\Integrations;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use App\Models\QboToken;
use App\Models\Contact;

class QboWebhookController extends Controller
{
    // Intuit sends batched event notifications. We'll verify signature and then queue processing; for now, process inline for Customers only.
    public function handle(Request $request)
    {
        // Verify signature
        $sig = $request->header('intuit-signature');
        $token = config('qbo.webhook_token');
        $raw = $request->getContent();
        if (!$sig || !$token || !hash_equals(base64_encode(hash_hmac('sha256', $raw, $token, true)), $sig)) {
            Log::warning('QBO webhook signature invalid');
            return response()->json(['ok' => false], 401);
        }

        $payload = $request->json()->all();
        Log::info('QBO webhook received', ['payload' => $payload]);

        $events = $payload['eventNotifications'] ?? [];
        foreach ($events as $ev) {
            $realmId = $ev['realmId'] ?? null;
            foreach (($ev['dataChangeEvent']['entities'] ?? []) as $entity) {
                if (($entity['name'] ?? '') !== 'Customer') continue;
                $id = $entity['id'] ?? null;
                $operation = $entity['operation'] ?? '';
                if (!$id || !$realmId) continue;
                try {
                    $this->syncCustomer($realmId, $id);
                } catch (\Throwable $e) {
                    Log::error('QBO webhook sync error', ['id' => $id, 'error' => $e->getMessage()]);
                }
            }
        }
        return response()->json(['ok' => true]);
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
        if ($company !== '') return ['first' => $first ?: null, 'last' => $last ?: null, 'company' => $company];
        if ($first !== '' || $last !== '') return ['first' => $first ?: null, 'last' => $last ?: null, 'company' => null];
        if ($display !== '') {
            if (str_contains($display, ',')) { [$l,$f] = array_map('trim', explode(',', $display, 2)); return ['first'=>$f?:null,'last'=>$l?:null,'company'=>null]; }
            $parts = preg_split('/\s+/', $display); if (count($parts)>=2){ $f=array_shift($parts); $l=implode(' ', $parts); return ['first'=>$f?:null,'last'=>$l?:null,'company'=>null]; }
            return ['first'=>$display,'last'=>null,'company'=>null];
        }
        return ['first'=>'Customer','last'=>(string)($c['Id']??''),'company'=>null];
    }

    protected function syncCustomer(string $realmId, string $id): void
    {
        $url = $this->baseUrl($realmId).'/customer/'.$id;
        $res = Http::withHeaders($this->authHeaders())->get($url, ['minorversion' => 65]);
        if ($res->status() === 401 || str_contains($res->body(), 'Token expired')) {
            $this->refreshTokenIfNeeded();
            $res = Http::withHeaders($this->authHeaders())->get($url, ['minorversion' => 65]);
        }
        if (!$res->ok()) throw new \RuntimeException('Fetch failed: '.$res->body());
        $c = $res->json()['Customer'] ?? [];
        if (!$c) return;
        $addr = $c['BillAddr'] ?? [];
        $email = $c['PrimaryEmailAddr']['Address'] ?? null;
        $phone = $c['PrimaryPhone']['FreeFormNumber'] ?? null;
        $mobile = $c['Mobile']['FreeFormNumber'] ?? null;
        $names = $this->mapNames($c);

        $contact = Contact::firstOrNew(['qbo_customer_id' => $c['Id']]);
        $contact->fill([
            'first_name' => $names['first'],
            'last_name' => $names['last'],
            'company_name' => $names['company'],
            'contact_type' => $contact->contact_type ?: 'client',
            'email' => $email,
            'phone' => $phone,
            'mobile' => $mobile,
            'address' => $addr['Line1'] ?? null,
            'city' => $addr['City'] ?? null,
            'state' => $addr['CountrySubDivisionCode'] ?? null,
            'postal_code' => $addr['PostalCode'] ?? null,
        ]);
        $contact->qbo_sync_token = $c['SyncToken'] ?? $contact->qbo_sync_token;
        $contact->qbo_last_synced_at = now();
        $contact->save();
    }
}
