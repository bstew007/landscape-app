<?php

namespace App\Services;

use App\Models\EstimatePurchaseOrder;
use App\Models\QboToken;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class QboPurchaseOrderService
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
        return [
            'Authorization' => 'Bearer ' . $token->access_token,
            'Accept' => 'application/json',
            'Content-Type' => 'application/json'
        ];
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

    /**
     * Create or update a Purchase Order in QuickBooks Online.
     *
     * @param EstimatePurchaseOrder $po
     * @return array ['success' => bool, 'qbo_id' => string|null, 'message' => string]
     */
    public function syncPurchaseOrder(EstimatePurchaseOrder $po): array
    {
        $token = QboToken::latest('updated_at')->first();
        if (!$token) {
            return ['success' => false, 'qbo_id' => null, 'message' => 'QBO not connected'];
        }

        // Ensure supplier has QBO vendor ID
        if (!$po->supplier || !$po->supplier->qbo_vendor_id) {
            return ['success' => false, 'qbo_id' => null, 'message' => 'Supplier must be synced to QBO first'];
        }

        // Load items
        $po->load(['items.material', 'items.estimateItem']);

        $isUpdate = (bool) $po->qbo_id;

        try {
            if ($isUpdate) {
                return $this->updatePurchaseOrder($po, $token);
            } else {
                return $this->createPurchaseOrder($po, $token);
            }
        } catch (\Exception $e) {
            if (config('qbo.debug')) {
                Log::error('QBO PO Sync Error', [
                    'po_id' => $po->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
            return ['success' => false, 'qbo_id' => null, 'message' => $e->getMessage()];
        }
    }

    /**
     * Create a new Purchase Order in QBO.
     */
    protected function createPurchaseOrder(EstimatePurchaseOrder $po, QboToken $token): array
    {
        $lineItems = [];
        
        foreach ($po->items as $item) {
            $materialName = $item->material_name;
            $description = $item->notes ?: $item->estimateItem?->description;
            
            $lineItems[] = [
                'DetailType' => 'ItemBasedExpenseLineDetail',
                'Amount' => (float) $item->total_cost,
                'ItemBasedExpenseLineDetail' => [
                    'ItemRef' => $this->ensureQboItem($token, $materialName),
                    'Qty' => (float) $item->quantity,
                    'UnitPrice' => (float) $item->unit_cost,
                ],
                'Description' => $description ? substr($description, 0, 4000) : null,
            ];
        }

        $payload = [
            'VendorRef' => [
                'value' => $po->supplier->qbo_vendor_id
            ],
            'Line' => $lineItems,
            'TotalAmt' => (float) $po->total_amount,
            'PrivateNote' => $po->notes ? substr($po->notes, 0, 4000) : null,
            'DocNumber' => $po->po_number,
        ];

        $payload = $this->clean($payload);

        $url = $this->baseUrl($token->realm_id) . '/purchaseorder';
        $response = Http::withHeaders($this->authHeaders())
            ->post($url, $payload);

        if (config('qbo.debug')) {
            Log::info('QBO PO Create', [
                'po_id' => $po->id,
                'status' => $response->status(),
                'tid' => $response->header('intuit_tid'),
                'request' => $payload,
                'response' => $response->body(),
            ]);
        }

        // Handle 401 token expiration
        if ($response->status() === 401) {
            $this->refreshTokenIfNeeded();
            $response = Http::withHeaders($this->authHeaders())
                ->post($url, $payload);
                
            if (config('qbo.debug')) {
                Log::info('QBO PO Create Retry', [
                    'po_id' => $po->id,
                    'status' => $response->status(),
                    'tid' => $response->header('intuit_tid'),
                    'response' => $response->body(),
                ]);
            }
        }

        if ($response->successful()) {
            $data = $response->json();
            $qboPO = $data['PurchaseOrder'] ?? null;
            
            if ($qboPO) {
                $qboId = $qboPO['Id'];
                
                // Update local record
                $po->update([
                    'qbo_id' => $qboId,
                    'qbo_synced_at' => now(),
                ]);
                
                return [
                    'success' => true,
                    'qbo_id' => $qboId,
                    'message' => "Purchase Order #{$po->po_number} created in QuickBooks"
                ];
            }
        }

        $error = $response->json()['Fault']['Error'][0]['Detail'] ?? 'Unknown error';
        return ['success' => false, 'qbo_id' => null, 'message' => $error];
    }

    /**
     * Update an existing Purchase Order in QBO.
     */
    protected function updatePurchaseOrder(EstimatePurchaseOrder $po, QboToken $token): array
    {
        // Fetch current PO to get SyncToken
        $getUrl = $this->baseUrl($token->realm_id) . '/purchaseorder/' . $po->qbo_id;
        $getResponse = Http::withHeaders($this->authHeaders())->get($getUrl);

        if (config('qbo.debug')) {
            Log::info('QBO PO Fetch for Update', [
                'po_id' => $po->id,
                'qbo_id' => $po->qbo_id,
                'status' => $getResponse->status(),
                'response' => $getResponse->body(),
            ]);
        }

        if ($getResponse->status() === 401) {
            $this->refreshTokenIfNeeded();
            $getResponse = Http::withHeaders($this->authHeaders())->get($getUrl);
        }

        if (!$getResponse->successful()) {
            return ['success' => false, 'qbo_id' => null, 'message' => 'Failed to fetch existing PO from QBO'];
        }

        $existingPO = $getResponse->json()['PurchaseOrder'] ?? null;
        if (!$existingPO) {
            return ['success' => false, 'qbo_id' => null, 'message' => 'PO not found in QBO'];
        }

        $syncToken = $existingPO['SyncToken'];

        // Build line items
        $lineItems = [];
        foreach ($po->items as $item) {
            $materialName = $item->material_name;
            $description = $item->notes ?: $item->estimateItem?->description;
            
            $lineItems[] = [
                'DetailType' => 'ItemBasedExpenseLineDetail',
                'Amount' => (float) $item->total_cost,
                'ItemBasedExpenseLineDetail' => [
                    'ItemRef' => $this->ensureQboItem($token, $materialName),
                    'Qty' => (float) $item->quantity,
                    'UnitPrice' => (float) $item->unit_cost,
                ],
                'Description' => $description ? substr($description, 0, 4000) : null,
            ];
        }

        $payload = [
            'Id' => $po->qbo_id,
            'SyncToken' => $syncToken,
            'VendorRef' => [
                'value' => $po->supplier->qbo_vendor_id
            ],
            'Line' => $lineItems,
            'TotalAmt' => (float) $po->total_amount,
            'PrivateNote' => $po->notes ? substr($po->notes, 0, 4000) : null,
            'DocNumber' => $po->po_number,
            'sparse' => true,
        ];

        $payload = $this->clean($payload);

        $url = $this->baseUrl($token->realm_id) . '/purchaseorder';
        $response = Http::withHeaders($this->authHeaders())
            ->post($url, $payload);

        if (config('qbo.debug')) {
            Log::info('QBO PO Update', [
                'po_id' => $po->id,
                'status' => $response->status(),
                'tid' => $response->header('intuit_tid'),
                'request' => $payload,
                'response' => $response->body(),
            ]);
        }

        if ($response->successful()) {
            $data = $response->json();
            $qboPO = $data['PurchaseOrder'] ?? null;
            
            if ($qboPO) {
                $po->update([
                    'qbo_synced_at' => now(),
                ]);
                
                return [
                    'success' => true,
                    'qbo_id' => $po->qbo_id,
                    'message' => "Purchase Order #{$po->po_number} updated in QuickBooks"
                ];
            }
        }

        $error = $response->json()['Fault']['Error'][0]['Detail'] ?? 'Unknown error';
        return ['success' => false, 'qbo_id' => null, 'message' => $error];
    }

    /**
     * Ensure a QBO Item exists for the material (creates a non-inventory item if needed).
     */
    protected function ensureQboItem(QboToken $token, string $itemName): array
    {
        // Try to find existing item
        $query = "select Id, Name from Item where Name = '" . addslashes($itemName) . "' and Type = 'NonInventory'";
        $searchUrl = $this->baseUrl($token->realm_id) . '/query';
        
        $response = Http::withHeaders($this->authHeaders())
            ->get($searchUrl, ['query' => $query]);

        if ($response->successful()) {
            $items = $response->json()['QueryResponse']['Item'] ?? [];
            if (!empty($items)) {
                return ['value' => $items[0]['Id']];
            }
        }

        // Item doesn't exist, create it as NonInventory
        $incomeAccount = $this->getDefaultIncomeAccount($token);
        $expenseAccount = $this->getDefaultExpenseAccount($token);

        $payload = [
            'Name' => substr($itemName, 0, 100), // QBO limit
            'Type' => 'NonInventory',
            'IncomeAccountRef' => $incomeAccount,
            'ExpenseAccountRef' => $expenseAccount,
        ];

        $createUrl = $this->baseUrl($token->realm_id) . '/item';
        $response = Http::withHeaders($this->authHeaders())
            ->post($createUrl, $this->clean($payload));

        if (config('qbo.debug')) {
            Log::info('QBO Item Create', [
                'name' => $itemName,
                'status' => $response->status(),
                'response' => $response->body(),
            ]);
        }

        if ($response->successful()) {
            $item = $response->json()['Item'] ?? null;
            if ($item) {
                return ['value' => $item['Id']];
            }
        }

        // Fallback: return a generic item reference (you may want to handle this differently)
        return ['value' => '1']; // Default to first item - adjust as needed
    }

    /**
     * Get default income account (Sales/Revenue).
     */
    protected function getDefaultIncomeAccount(QboToken $token): array
    {
        $query = "select Id, Name from Account where AccountType = 'Income' maxresults 1";
        $response = Http::withHeaders($this->authHeaders())
            ->get($this->baseUrl($token->realm_id) . '/query', ['query' => $query]);

        if ($response->successful()) {
            $accounts = $response->json()['QueryResponse']['Account'] ?? [];
            if (!empty($accounts)) {
                return ['value' => $accounts[0]['Id']];
            }
        }

        return ['value' => '1']; // Fallback
    }

    /**
     * Get default expense account (Cost of Goods Sold or Supplies).
     */
    protected function getDefaultExpenseAccount(QboToken $token): array
    {
        $query = "select Id, Name from Account where AccountType = 'Cost of Goods Sold' maxresults 1";
        $response = Http::withHeaders($this->authHeaders())
            ->get($this->baseUrl($token->realm_id) . '/query', ['query' => $query]);

        if ($response->successful()) {
            $accounts = $response->json()['QueryResponse']['Account'] ?? [];
            if (!empty($accounts)) {
                return ['value' => $accounts[0]['Id']];
            }
        }

        // Fallback to Expense type
        $query = "select Id, Name from Account where AccountType = 'Expense' maxresults 1";
        $response = Http::withHeaders($this->authHeaders())
            ->get($this->baseUrl($token->realm_id) . '/query', ['query' => $query]);

        if ($response->successful()) {
            $accounts = $response->json()['QueryResponse']['Account'] ?? [];
            if (!empty($accounts)) {
                return ['value' => $accounts[0]['Id']];
            }
        }

        return ['value' => '1']; // Fallback
    }

    /**
     * Delete (void) a Purchase Order in QBO.
     */
    public function deletePurchaseOrder(EstimatePurchaseOrder $po): array
    {
        $token = QboToken::latest('updated_at')->first();
        if (!$token) {
            return ['success' => false, 'message' => 'QBO not connected'];
        }

        if (!$po->qbo_id) {
            return ['success' => false, 'message' => 'PO not synced to QBO'];
        }

        // Fetch to get SyncToken
        $getUrl = $this->baseUrl($token->realm_id) . '/purchaseorder/' . $po->qbo_id;
        $getResponse = Http::withHeaders($this->authHeaders())->get($getUrl);

        if (!$getResponse->successful()) {
            return ['success' => false, 'message' => 'Failed to fetch PO from QBO'];
        }

        $existingPO = $getResponse->json()['PurchaseOrder'] ?? null;
        if (!$existingPO) {
            return ['success' => false, 'message' => 'PO not found in QBO'];
        }

        $syncToken = $existingPO['SyncToken'];

        // Delete via sparse update (set to Deleted status is not standard; use ?operation=delete)
        $deleteUrl = $this->baseUrl($token->realm_id) . '/purchaseorder?operation=delete';
        $payload = [
            'Id' => $po->qbo_id,
            'SyncToken' => $syncToken,
        ];

        $response = Http::withHeaders($this->authHeaders())
            ->post($deleteUrl, $this->clean($payload));

        if (config('qbo.debug')) {
            Log::info('QBO PO Delete', [
                'po_id' => $po->id,
                'qbo_id' => $po->qbo_id,
                'status' => $response->status(),
                'response' => $response->body(),
            ]);
        }

        if ($response->successful()) {
            // Clear QBO linkage
            $po->update([
                'qbo_id' => null,
                'qbo_synced_at' => null,
            ]);

            return [
                'success' => true,
                'message' => "Purchase Order #{$po->po_number} deleted from QuickBooks"
            ];
        }

        $error = $response->json()['Fault']['Error'][0]['Detail'] ?? 'Unknown error';
        return ['success' => false, 'message' => $error];
    }
}
