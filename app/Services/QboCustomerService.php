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

    protected function phonePayload(?string $number): ?array
    {
        if (!$number) return null;
        $digits = preg_replace('/[^0-9]/', '', $number);
        if (strlen($digits) >= 11 && str_starts_with($digits, '1')) {
            $digits = substr($digits, 1);
        }
        if (strlen($digits) > 10) {
            $digits = substr($digits, 0, 10);
        }
        if (strlen($digits) < 7) return null; // avoid junk numbers
        return ['FreeFormNumber' => $digits];
    }

    public function upsert(Contact $c): array
    {
        $token = QboToken::latest('updated_at')->first();
        if (!$token) throw new \RuntimeException('QBO not connected');

        // Build a base payload for create operations (full set)
        $base = [
            'DisplayName' => $c->company_name ?: trim($c->first_name.' '.$c->last_name) ?: ($c->email ?: 'Customer '.($c->id)),
            'CompanyName' => $c->company_name ?: null,
            'GivenName' => $c->first_name ?: null,
            'FamilyName' => $c->last_name ?: null,
            'PrimaryEmailAddr' => $c->email ? ['Address' => $c->email] : null,
            'PrimaryPhone' => $this->phonePayload($c->phone),
            // Note: Mobile has been a frequent cause of 2010 ValidationFaults on update; only send on create
            'Mobile' => $this->phonePayload($c->mobile),
            'BillAddr' => [
                'Line1' => $c->address ?: null,
                'City' => $c->city ?: null,
                'CountrySubDivisionCode' => $c->state ?: null,
                'PostalCode' => $c->postal_code ?: null,
            ],
        ];
        $base = $this->clean($base);

        $url = $this->baseUrl($token->realm_id).'/customer';

        $isUpdate = (bool) $c->qbo_customer_id;
        $query = ['minorversion' => 65] + ($isUpdate ? ['operation' => 'update'] : []);

        // Helper to deep-compare QBO structures after cleaning
        $valuesEqual = function ($a, $b): bool {
            $cleanA = $this->clean($a);
            $cleanB = $this->clean($b);
            return json_encode($cleanA) === json_encode($cleanB);
        };

        // Prepare payload depending on create vs update
        $payload = null;
        $existing = null;

        if ($isUpdate) {
            // Fetch to get fresh SyncToken and existing values
            $get = Http::withHeaders($this->authHeaders())
                ->get($this->baseUrl($token->realm_id).'/customer/'.$c->qbo_customer_id, ['minorversion' => 65]);
            if (config('qbo.debug')) {
                \Log::info('QBO fetch before update', [
                    'status' => $get->status(),
                    'tid' => $get->header('intuit_tid'),
                    'body' => $get->body(),
                ]);
            }
            if ($get->status() === 401) {
                // Access token likely expired; refresh and retry fetch to obtain SyncToken
                $this->refreshTokenIfNeeded();
                $get = Http::withHeaders($this->authHeaders())
                    ->get($this->baseUrl($token->realm_id).'/customer/'.$c->qbo_customer_id, ['minorversion' => 65]);
                if (config('qbo.debug')) {
                    \Log::info('QBO fetch retry after refresh', [
                        'status' => $get->status(),
                        'tid' => $get->header('intuit_tid'),
                        'body' => $get->body(),
                    ]);
                }
            }
            if ($get->ok()) {
                $existing = $get->json()['Customer'] ?? null;
            } else if ($get->status() === 401) {
                throw new \RuntimeException('QBO auth failed while fetching customer for update');
            }

            // Minimal sparse update: only include allowed, changed fields
            $allowedUpdateKeys = ['PrimaryEmailAddr', 'PrimaryPhone', 'BillAddr']; // exclude names and Mobile on update
            $updateBody = [];
            foreach ($allowedUpdateKeys as $key) {
                if (!array_key_exists($key, $base)) continue;
                $newVal = $base[$key] ?? null;
                // Normalize comparison for BillAddr to the keys we actually send
                if ($key === 'BillAddr') {
                    $oldAddr = $existing['BillAddr'] ?? [];
                    $oldVal = [
                        'Line1' => $oldAddr['Line1'] ?? null,
                        'City' => $oldAddr['City'] ?? null,
                        'CountrySubDivisionCode' => $oldAddr['CountrySubDivisionCode'] ?? null,
                        'PostalCode' => $oldAddr['PostalCode'] ?? null,
                    ];
                } else {
                    $oldVal = $existing[$key] ?? null;
                }
                if ($newVal === null) continue; // don't send nulls in sparse updates
                if ($existing === null || !$valuesEqual($newVal, $oldVal)) {
                    $updateBody[$key] = $newVal;
                }
            }

            // If nothing to update in allowed fields
            if (empty($updateBody)) {
                // If excluded fields (names or Mobile) changed, do NOT mark as synced so UI shows Needs Sync
                $excludedChanged = false;
                $nameKeys = ['DisplayName','CompanyName','GivenName','FamilyName'];
                foreach ($nameKeys as $nk) {
                    $newVal = $base[$nk] ?? null;
                    $oldVal = $existing[$nk] ?? null;
                    if ($newVal !== null && $newVal !== $oldVal) { $excludedChanged = true; break; }
                }
                if (!$excludedChanged) {
                    $newMobile = $base['Mobile'] ?? null;
                    $oldMobile = $existing['Mobile'] ?? null;
                    if (!$valuesEqual($newMobile, $oldMobile)) { $excludedChanged = true; }
                }
                if ($excludedChanged) {
                    return ['Customer' => $existing ?: ['Id' => $c->qbo_customer_id], 'skipped' => 'excluded_fields_changed'];
                }
                // Otherwise, there truly is nothing to sync; mark synced timestamp
                $origTimestamps = $c->timestamps;
                $c->timestamps = false;
                $c->qbo_last_synced_at = now();
                $c->save();
                $c->timestamps = $origTimestamps;
                return ['Customer' => $existing ?: ['Id' => $c->qbo_customer_id]];
            }

            $payload = (object) array_merge($updateBody, [
                'Id' => $c->qbo_customer_id,
                'SyncToken' => $existing['SyncToken'] ?? $c->qbo_sync_token ?? '0',
                'sparse' => true,
            ]);
        } else {
            // Create with the full base payload
            $payload = json_decode(json_encode($base));
        }

        if (config('qbo.debug')) {
            try {
                $pp = is_object($payload) && property_exists($payload, 'PrimaryPhone') ? $payload->PrimaryPhone : (is_array($payload) && isset($payload['PrimaryPhone']) ? $payload['PrimaryPhone'] : null);
                \Log::info('QBO upsert request (payload keys)', [
                    'is_update' => $isUpdate,
                    'keys' => is_object($payload) ? array_keys(get_object_vars($payload)) : (is_array($payload) ? array_keys($payload) : []),
                    'primary_phone' => $pp,
                ]);
            } catch (\Throwable $e) {}
        }
        if (config('qbo.debug')) {
            try {
                $pp = is_object($payload) && property_exists($payload, 'PrimaryPhone') ? $payload->PrimaryPhone : (is_array($payload) && isset($payload['PrimaryPhone']) ? $payload['PrimaryPhone'] : null);
                \Log::info('QBO upsert request (payload keys)', [
                    'is_update' => $isUpdate,
                    'keys' => is_object($payload) ? array_keys(get_object_vars($payload)) : (is_array($payload) ? array_keys($payload) : []),
                    'PrimaryPhone' => $pp,
                ]);
            } catch (\Throwable $e) {}
        }
        $res = Http::withHeaders($this->authHeaders())
            ->withOptions(['query' => $query])
            ->post($url, $payload);

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
                    if (config('qbo.debug')) {
            try {
                $pp = is_object($payload) && property_exists($payload, 'PrimaryPhone') ? $payload->PrimaryPhone : (is_array($payload) && isset($payload['PrimaryPhone']) ? $payload['PrimaryPhone'] : null);
                \Log::info('QBO upsert request (payload keys)', [
                    'is_update' => $isUpdate,
                    'keys' => is_object($payload) ? array_keys(get_object_vars($payload)) : (is_array($payload) ? array_keys($payload) : []),
                    'primary_phone' => $pp,
                ]);
            } catch (\Throwable $e) {}
        }
        if (config('qbo.debug')) {
            try {
                $pp = is_object($payload) && property_exists($payload, 'PrimaryPhone') ? $payload->PrimaryPhone : (is_array($payload) && isset($payload['PrimaryPhone']) ? $payload['PrimaryPhone'] : null);
                \Log::info('QBO upsert request (payload keys)', [
                    'is_update' => $isUpdate,
                    'keys' => is_object($payload) ? array_keys(get_object_vars($payload)) : (is_array($payload) ? array_keys($payload) : []),
                    'PrimaryPhone' => $pp,
                ]);
            } catch (\Throwable $e) {}
        }
        $res = Http::withHeaders($this->authHeaders())
            ->withOptions(['query' => $query])
            ->post($url, $payload);
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

    public function updateMobile(Contact $c): array
    {
        if (!$c->qbo_customer_id) throw new \RuntimeException('Contact is not linked to QBO');
        $token = QboToken::latest('updated_at')->first();
        if (!$token) throw new \RuntimeException('QBO not connected');
        $realmId = $token->realm_id;

        // Fetch existing to get SyncToken
        $get = Http::withHeaders($this->authHeaders())
            ->get($this->baseUrl($realmId).'/customer/'.$c->qbo_customer_id, ['minorversion' => 65]);
        if ($get->status() === 401) {
            $this->refreshTokenIfNeeded();
            $get = Http::withHeaders($this->authHeaders())
                ->get($this->baseUrl($realmId).'/customer/'.$c->qbo_customer_id, ['minorversion' => 65]);
        }
        if (!$get->ok()) throw new \RuntimeException('QBO fetch failed: '.$get->body());
        $existing = $get->json()['Customer'] ?? [];

        $newMobile = $this->phonePayload($c->mobile);
        if ($newMobile === null) throw new \RuntimeException('Mobile is empty or invalid.');
        $oldMobile = $existing['Mobile']['FreeFormNumber'] ?? null;
        if ($oldMobile === ($newMobile['FreeFormNumber'] ?? null)) {
            $origTimestamps = $c->timestamps; $c->timestamps = false; $c->qbo_last_synced_at = now(); $c->save(); $c->timestamps = $origTimestamps;
            return ['Customer' => $existing];
        }

        $payload = (object) [
            'Id' => $c->qbo_customer_id,
            'SyncToken' => $existing['SyncToken'] ?? $c->qbo_sync_token ?? '0',
            'sparse' => true,
            'Mobile' => $newMobile,
        ];
        $query = ['minorversion' => 65, 'operation' => 'update'];
        $url = $this->baseUrl($realmId).'/customer';
        $res = Http::withHeaders($this->authHeaders())
            ->withOptions(['query' => $query])
            ->post($url, $payload);
        if ($res->status() === 401) {
            $this->refreshTokenIfNeeded();
            $res = Http::withHeaders($this->authHeaders())
                ->withOptions(['query' => $query])
                ->post($url, $payload);
        }
        if (!$res->ok()) throw new \RuntimeException('QBO Mobile update failed: '.$res->body());
        $customer = $res->json()['Customer'] ?? null;
        if ($customer) {
            $c->qbo_sync_token = $customer['SyncToken'] ?? $c->qbo_sync_token;
            $c->qbo_last_synced_at = now();
            $origTimestamps = $c->timestamps; $c->timestamps = false; $c->save(); $c->timestamps = $origTimestamps;
        }
        return $res->json();
    }

    public function updateNames(Contact $c): array
    {
        if (!$c->qbo_customer_id) throw new \RuntimeException('Contact is not linked to QBO');
        $token = QboToken::latest('updated_at')->first();
        if (!$token) throw new \RuntimeException('QBO not connected');
        $realmId = $token->realm_id;

        $get = Http::withHeaders($this->authHeaders())
            ->get($this->baseUrl($realmId).'/customer/'.$c->qbo_customer_id, ['minorversion' => 65]);
        if ($get->status() === 401) {
            $this->refreshTokenIfNeeded();
            $get = Http::withHeaders($this->authHeaders())
                ->get($this->baseUrl($realmId).'/customer/'.$c->qbo_customer_id, ['minorversion' => 65]);
        }
        if (!$get->ok()) throw new \RuntimeException('QBO fetch failed: '.$get->body());
        $existing = $get->json()['Customer'] ?? [];

        $desired = $this->clean([
            'DisplayName' => $c->company_name ?: trim(($c->first_name ?? '').' '.($c->last_name ?? '')) ?: ($c->email ?: 'Customer '.($c->id)),
            'CompanyName' => $c->company_name ?: null,
            'GivenName' => $c->first_name ?: null,
            'FamilyName' => $c->last_name ?: null,
        ]);

        $updateBody = [];
        foreach (['DisplayName','CompanyName','GivenName','FamilyName'] as $k) {
            $newVal = $desired[$k] ?? null;
            $oldVal = $existing[$k] ?? null;
            if ($newVal === null) continue;
            if ($newVal !== $oldVal) $updateBody[$k] = $newVal;
        }
        if (empty($updateBody)) {
            $origTimestamps = $c->timestamps; $c->timestamps = false; $c->qbo_last_synced_at = now(); $c->save(); $c->timestamps = $origTimestamps;
            return ['Customer' => $existing];
        }

        $payload = (object) array_merge($updateBody, [
            'Id' => $c->qbo_customer_id,
            'SyncToken' => $existing['SyncToken'] ?? $c->qbo_sync_token ?? '0',
            'sparse' => true,
        ]);

        $query = ['minorversion' => 65, 'operation' => 'update'];
        $url = $this->baseUrl($realmId).'/customer';
        $res = Http::withHeaders($this->authHeaders())
            ->withOptions(['query' => $query])
            ->post($url, $payload);
        if ($res->status() === 401) {
            $this->refreshTokenIfNeeded();
            $res = Http::withHeaders($this->authHeaders())
                ->withOptions(['query' => $query])
                ->post($url, $payload);
        }
        if (!$res->ok()) {
            $body = $res->json();
            $code = $body['Fault']['Error'][0]['code'] ?? null;
            if ($code === '6240') { // Duplicate name exists
                throw new \RuntimeException('QBO rejected the name: duplicate DisplayName exists. Please choose a unique name.');
            }
            throw new \RuntimeException('QBO name update failed: '.$res->body());
        }
        $customer = $res->json()['Customer'] ?? null;
        if ($customer) {
            $c->qbo_sync_token = $customer['SyncToken'] ?? $c->qbo_sync_token;
            $c->qbo_last_synced_at = now();
            $origTimestamps = $c->timestamps; $c->timestamps = false; $c->save(); $c->timestamps = $origTimestamps;
        }
        return $res->json();
    }
}
