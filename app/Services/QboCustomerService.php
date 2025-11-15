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
            'BillAddr' => [
                'Line1' => $c->address ?: null,
                'City' => $c->city ?: null,
                'CountrySubDivisionCode' => $c->state ?: null,
                'PostalCode' => $c->postal_code ?: null,
            ],
        ];
        // Remove nulls
        $payload = json_decode(json_encode($payload));

        $url = $this->baseUrl($token->realm_id).'/customer';

        if ($c->qbo_customer_id) {
            // Update requires SyncToken; try fetch first
            $get = Http::withHeaders($this->authHeaders())->get($this->baseUrl($token->realm_id).'/customer/'.$c->qbo_customer_id);
            if ($get->ok()) {
                $cust = $get->json()['Customer'] ?? null;
                $payload->Id = $c->qbo_customer_id;
                $payload->SyncToken = $cust['SyncToken'] ?? $c->qbo_sync_token ?? '0';
            }
        }

        $res = Http::withHeaders($this->authHeaders())->post($url, [ 'Customer' => $payload ]);
        if (!$res->ok()) {
            throw new \RuntimeException('QBO Customer upsert failed: '.$res->body());
        }
        $customer = $res->json()['Customer'] ?? null;
        if ($customer) {
            $c->qbo_customer_id = $customer['Id'] ?? $c->qbo_customer_id;
            $c->qbo_sync_token = $customer['SyncToken'] ?? $c->qbo_sync_token;
            $c->qbo_last_synced_at = now();
            $c->save();
        }
        return $res->json();
    }
}
