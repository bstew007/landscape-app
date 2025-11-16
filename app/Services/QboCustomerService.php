<?php

namespace App\Services;

use App\Models\Contact;
use App\Models\QboToken;
use Illuminate\Support\Facades\Http;

class QboCustomerService
{
    protected function baseUrl(string $realmId): string
    {
        $env = config('qbo.environment');
        $host = $env === 'production' ? 'quickbooks.api.intuit.com' : 'sandbox-quickbooks.api.intuit.com';
        return "https://{$host}/v3/company/{$realmId}";
    }

    protected function authHeaders(): array
    {
        $token = QboToken::latest('updated_at')->first();
        return ['Authorization' => 'Bearer '.$token->access_token, 'Accept' => 'application/json', 'Content-Type' => 'application/json'];
    }

    protected function clean($value)
    {
        if (is_array($value)) {
            $out = [];
            foreach ($value as $k => $v) {
                $cv = $this->clean($v);
                if ($cv === null) continue;
                if (is_array($cv) && empty($cv)) continue;
                $out[$k] = $cv;
            }
            return $out;
        }
        if (is_object($value)) {
            $vars = get_object_vars($value);
            $cleaned = $this->clean($vars);
            return (object) $cleaned;
        }
        return $value;
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

    public function upsert(Contact $c): array
    {
        $token = QboToken::latest('updated_at')->first();
        if (!$token) throw new \RuntimeException('QBO not connected');

        $payload = [
            'DisplayName' => $c->company_name ?: trim($c->first_name.' '.$c->last_name) ?: ($c->email ?: 'Customer '.($c->id)),
            'CompanyName' => $c->company_name ?: null,
            'GivenName' => $c->first_name ?: null,
            'FamilyName' => $c->last_name ?: null,
            'PrimaryEmailAddr' => $c->email ? ['Address' => $c->email] : null,
            'PrimaryPhone' => $c->phone ? ['FreeFormNumber' => $c->phone] : null,
            'Mobile' => $c->mobile ? ['FreeFormNumber' => $c->mobile] : null,
            'BillAddr' => [
                'Line1' => $c->address ?: null,
                'City' => $c->city ?: null,
                'CountrySubDivisionCode' => $c->state ?: null,
                'PostalCode' => $c->postal_code ?: null,
            ],
        ];
        // Remove nulls/empties thoroughly
        $payload = $this->clean($payload);
        $payload = json_decode(json_encode($payload));

        $url = $this->baseUrl($token->realm_id).'/customer';

        $isUpdate = false;
        if ($c->qbo_customer_id) {
            $isUpdate = true;
            // Update requires SyncToken; try fetch first
            $get = Http::withHeaders($this->authHeaders())
                ->get($this->baseUrl($token->realm_id).'/customer/'.$c->qbo_customer_id, ['minorversion' => 65]);
            if (config('qbo.debug')) {
                \Log::info('QBO fetch before update', [
                    'status' => $get->status(),
                    'tid' => $get->header('intuit_tid'),
                    'body' => $get->body(),
                ]);
            }
            if ($get->ok()) {
                $cust = $get->json()['Customer'] ?? null;
                $payload->Id = $c->qbo_customer_id;
                $payload->SyncToken = $cust['SyncToken'] ?? $c->qbo_sync_token ?? '0';
                // Use sparse update to avoid unintended overwrites
                $payload->sparse = true;
            }
        }

        // Include minorversion and operation=update on POST when updating
        $query = ['minorversion' => 65];
        if ($isUpdate) { $query['operation'] = 'update'; }

        $res = Http::withHeaders($this->authHeaders())
            ->withOptions(['query' => $query])
            ->post($url, [ 'Customer' => $payload ]);
        if (config('qbo.debug')) {
            \Log::info('QBO upsert response', [
                'status' => $res->status(),
                'tid' => $res->header('intuit_tid'),
                'body' => $res->body(),
                'query' => $query,
            ]);
        }
        if ($res->status() === 401 || str_contains($res->body(), 'Token expired')) {
            $this->refreshTokenIfNeeded();
            $res = Http::withHeaders($this->authHeaders())
                ->withOptions(['query' => $query])
                ->post($url, [ 'Customer' => $payload ]);
            if (config('qbo.debug')) {
                \Log::warning('QBO upsert retry after refresh', [
                    'status' => $res->status(),
                    'tid' => $res->header('intuit_tid'),
                    'body' => $res->body(),
                ]);
            }
        }
        if (!$res->ok()) {
            throw new \RuntimeException('QBO Customer upsert failed: '.$res->body());
        }
        $customer = $res->json()['Customer'] ?? null;
        if ($customer) {
            $c->qbo_customer_id = $customer['Id'] ?? $c->qbo_customer_id;
            $c->qbo_sync_token = $customer['SyncToken'] ?? $c->qbo_sync_token;
            $c->qbo_last_synced_at = now();
            // Do not bump updated_at when only changing qbo_* fields so status shows Synced
            $origTimestamps = $c->timestamps;
            $c->timestamps = false;
            $c->save();
            $c->timestamps = $origTimestamps;
        }
        return $res->json();
    }
}
