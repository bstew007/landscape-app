<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\QboToken;
use Illuminate\Support\Facades\Http;

class QboInvoiceService
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

    protected function ensureServiceItem(string $realmId, string $itemName = 'Services'): array
    {
        // Try to find Item by name
        $q = [ 'query' => "select Id,Name,Type,IncomeAccountRef from Item where Name = '{$itemName}' and Type = 'Service'" ];
        $res = Http::withHeaders($this->authHeaders())
            ->get($this->baseUrl($realmId).'/query', $q);
        if (config('qbo.debug')) { \Log::info('QBO query item (Services)', ['status'=>$res->status(),'body'=>$res->body()]); }
        if ($res->status() === 401) { $this->refreshTokenIfNeeded(); $res = Http::withHeaders($this->authHeaders())->get($this->baseUrl($realmId).'/query', $q); }
        if ($res->ok() && !empty($res->json()['QueryResponse']['Item'][0])) {
            return $res->json()['QueryResponse']['Item'][0];
        }
        // Find an Income account to attach to the Service item
        $accQ = [ 'query' => "select Id,Name,AccountType from Account where AccountType in ('Income','OtherIncome') order by Id" ];
        $accRes = Http::withHeaders($this->authHeaders())
            ->get($this->baseUrl($realmId).'/query', $accQ);
        if ($accRes->status() === 401) { $this->refreshTokenIfNeeded(); $accRes = Http::withHeaders($this->authHeaders())->get($this->baseUrl($realmId).'/query', $accQ); }
        if (!$accRes->ok() || empty($accRes->json()['QueryResponse']['Account'][0])) {
            throw new \RuntimeException('Unable to locate an Income account in QBO for Service item.');
        }
        $incomeAccount = $accRes->json()['QueryResponse']['Account'][0];

        // Create Services item with the found Income account
        $payload = [
            'Name' => $itemName,
            'Type' => 'Service',
            'IncomeAccountRef' => ['value' => (string) $incomeAccount['Id'], 'name' => $incomeAccount['Name'] ?? 'Income']
        ];
        if (config('qbo.debug')) { \Log::info('QBO create item (Services) payload', ['payload'=>$payload]); }
        $res = Http::withHeaders($this->authHeaders())
            ->post($this->baseUrl($realmId).'/item', $payload);
        if ($res->status() === 401) { $this->refreshTokenIfNeeded(); $res = Http::withHeaders($this->authHeaders())->post($this->baseUrl($realmId).'/item', $payload); }
        if (!$res->ok()) throw new \RuntimeException('Failed to ensure Services item: '.$res->body());
        return $res->json()['Item'] ?? [];
    }

    protected function mapInvoiceToQbo(Invoice $invoice, array $serviceItem): array
    {
        $estimate = $invoice->estimate; $client = $estimate?->client; $token = QboToken::latest('updated_at')->first();
        if (!$client || !$client->qbo_customer_id) throw new \RuntimeException('Invoice client is not linked to QBO');
        $lines = [];
        // For Phase 1, one line with total amount as Services
        $amount = (float) ($invoice->amount ?? $estimate?->grand_total ?? 0);
        $lines[] = [
            'DetailType' => 'SalesItemLineDetail',
            'Amount' => $amount,
            'SalesItemLineDetail' => [ 'ItemRef' => ['value' => $serviceItem['Id'], 'name' => $serviceItem['Name'] ] ],
            'Description' => $estimate?->title ?: 'Services',
        ];
        $payload = [
            'CustomerRef' => ['value' => $client->qbo_customer_id],
            'TxnDate' => now()->toDateString(),
            'Line' => $lines,
        ];
        if (!empty($invoice->due_date)) { $payload['DueDate'] = $invoice->due_date->toDateString(); }
        if (!empty($client->address)) {
            $payload['BillAddr'] = [
                'Line1' => $client->address,
                'City' => $client->city,
                'CountrySubDivisionCode' => $client->state,
                'PostalCode' => $client->postal_code,
            ];
        }
        return $payload;
    }

    public function create(Invoice $invoice): array
    {
        $token = QboToken::latest('updated_at')->first();
        if (!$token) throw new \RuntimeException('QBO not connected');
        $realmId = $token->realm_id;
        $serviceItem = $this->ensureServiceItem($realmId, 'Services');
        $payload = $this->mapInvoiceToQbo($invoice, $serviceItem);
        $amount = $payload['Line'][0]['Amount'] ?? 0;
        if ($amount <= 0) {
            throw new \RuntimeException('Invoice total is zero. QBO requires a positive amount to create an invoice.');
        }
        if (config('qbo.debug')) { \Log::info('QBO create invoice payload', ['payload'=>$payload]); }
        $url = $this->baseUrl($realmId).'/invoice';
        $res = Http::withHeaders($this->authHeaders())
            ->withOptions(['query' => ['minorversion' => 65]])
            ->post($url, $payload);
        if ($res->status() === 401) {
            $this->refreshTokenIfNeeded();
            $res = Http::withHeaders($this->authHeaders())
                ->withOptions(['query' => ['minorversion' => 65]])
                ->post($url, $payload);
        }
        if (config('qbo.debug')) { \Log::info('QBO create invoice response', ['status'=>$res->status(),'tid'=>$res->header('intuit_tid'),'body'=>$res->body()]); }
        if (!$res->ok()) {
            // Retry with wrapped payload in case this tenant requires {"Invoice": {...}}
            $wrapped = ['Invoice' => $payload];
            $retry = Http::withHeaders($this->authHeaders())
                ->withOptions(['query' => ['minorversion' => 65]])
                ->post($url, $wrapped);
            if (config('qbo.debug')) { \Log::warning('QBO create invoice retry (wrapped)', ['status'=>$retry->status(),'tid'=>$retry->header('intuit_tid'),'body'=>$retry->body()]); }
            if (!$retry->ok()) {
                throw new \RuntimeException('QBO Invoice create failed: '.$retry->body());
            }
            $res = $retry;
        }
        $inv = $res->json()['Invoice'] ?? [];
        if ($inv) {
            $invoice->qbo_invoice_id = $inv['Id'] ?? null;
            $invoice->qbo_sync_token = $inv['SyncToken'] ?? null;
            $invoice->qbo_doc_number = $inv['DocNumber'] ?? null;
            $invoice->qbo_total = isset($inv['TotalAmt']) ? (float) $inv['TotalAmt'] : null;
            $invoice->qbo_balance = isset($inv['Balance']) ? (float) $inv['Balance'] : null;
            $invoice->qbo_status = $inv['PrivateNote'] ?? null; // placeholder; real status mapping below
            $invoice->qbo_last_synced_at = now();
            $invoice->save();
        }
        return $res->json();
    }

    public function refresh(Invoice $invoice): array
    {
        $token = QboToken::latest('updated_at')->first();
        if (!$token) throw new \RuntimeException('QBO not connected');
        if (!$invoice->qbo_invoice_id) throw new \RuntimeException('Invoice not linked to QBO');
        $realmId = $token->realm_id;
        $res = Http::withHeaders($this->authHeaders())
            ->get($this->baseUrl($realmId).'/invoice/'.$invoice->qbo_invoice_id, ['minorversion' => 65]);
        if ($res->status() === 401) { $this->refreshTokenIfNeeded(); $res = Http::withHeaders($this->authHeaders())->get($this->baseUrl($realmId).'/invoice/'.$invoice->qbo_invoice_id, ['minorversion' => 65]); }
        if (config('qbo.debug')) { \Log::info('QBO refresh invoice response', ['status'=>$res->status(),'tid'=>$res->header('intuit_tid'),'body'=>$res->body()]); }
        if (!$res->ok()) throw new \RuntimeException('QBO Invoice fetch failed: '.$res->body());
        $inv = $res->json()['Invoice'] ?? [];
        if ($inv) {
            $invoice->qbo_sync_token = $inv['SyncToken'] ?? $invoice->qbo_sync_token;
            $invoice->qbo_doc_number = $inv['DocNumber'] ?? $invoice->qbo_doc_number;
            $invoice->qbo_total = isset($inv['TotalAmt']) ? (float) $inv['TotalAmt'] : $invoice->qbo_total;
            $invoice->qbo_balance = isset($inv['Balance']) ? (float) $inv['Balance'] : $invoice->qbo_balance;
            $invoice->qbo_status = $inv['DeliveryInfo']['DeliveryType'] ?? $invoice->qbo_status; // placeholder
            $invoice->qbo_last_synced_at = now();
            $invoice->save();
        }
        return $res->json();
    }
}
