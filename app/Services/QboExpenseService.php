<?php

namespace App\Services;

use App\Models\AssetExpense;
use App\Models\QboToken;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class QboExpenseService
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
     * Sync an asset expense to QuickBooks Online as a Purchase (Expense).
     *
     * @param AssetExpense $expense
     * @return array ['success' => bool, 'qbo_id' => string|null, 'message' => string]
     */
    public function syncExpense(AssetExpense $expense): array
    {
        $token = QboToken::latest('updated_at')->first();
        if (!$token) {
            return ['success' => false, 'qbo_id' => null, 'message' => 'QBO not connected'];
        }

        // Ensure expense is approved
        if (!$expense->approved_by) {
            return ['success' => false, 'qbo_id' => null, 'message' => 'Expense must be approved before syncing to QBO'];
        }

        $expense->load(['asset', 'assetIssue', 'attachments']);

        $isUpdate = (bool) $expense->qbo_expense_id;

        try {
            if ($isUpdate) {
                return $this->updateExpense($expense, $token);
            } else {
                return $this->createExpense($expense, $token);
            }
        } catch (\Exception $e) {
            if (config('qbo.debug')) {
                Log::error('QBO Expense Sync Error', [
                    'expense_id' => $expense->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
            return ['success' => false, 'qbo_id' => null, 'message' => $e->getMessage()];
        }
    }

    /**
     * Create a new Purchase (Expense) in QBO.
     */
    protected function createExpense(AssetExpense $expense, QboToken $token): array
    {
        // Get or create expense account based on category
        $accountRef = $this->getExpenseAccount($token, $expense->category);
        
        // Build description
        $description = $this->buildDescription($expense);

        // Build line item
        $lineItems = [
            [
                'DetailType' => 'AccountBasedExpenseLineDetail',
                'Amount' => (float) $expense->amount,
                'AccountBasedExpenseLineDetail' => [
                    'AccountRef' => $accountRef,
                ],
                'Description' => $description,
            ]
        ];

        $payload = [
            'PaymentType' => 'Cash', // Can be Cash, Check, or CreditCard
            'AccountRef' => $accountRef, // Default expense account
            'Line' => $lineItems,
            'TotalAmt' => (float) $expense->amount,
            'PrivateNote' => $this->buildPrivateNote($expense),
            'DocNumber' => $expense->receipt_number,
            'TxnDate' => $expense->expense_date->format('Y-m-d'),
        ];

        // Add vendor if provided
        if ($expense->vendor) {
            $vendorRef = $this->ensureQboVendor($token, $expense->vendor);
            if ($vendorRef) {
                $payload['EntityRef'] = $vendorRef;
            }
        }

        $payload = $this->clean($payload);

        $url = $this->baseUrl($token->realm_id) . '/purchase';
        $response = Http::withHeaders($this->authHeaders())
            ->withHeader('Content-Type', 'application/json')
            ->post($url, $payload);

        if (config('qbo.debug')) {
            Log::info('QBO Expense Create', [
                'expense_id' => $expense->id,
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
                ->withHeader('Content-Type', 'application/json')
                ->post($url, $payload);
                
            if (config('qbo.debug')) {
                Log::info('QBO Expense Create Retry', [
                    'expense_id' => $expense->id,
                    'status' => $response->status(),
                    'tid' => $response->header('intuit_tid'),
                    'response' => $response->body(),
                ]);
            }
        }

        if ($response->successful()) {
            $data = $response->json();
            $qboExpense = $data['Purchase'] ?? null;
            
            if ($qboExpense) {
                $qboId = $qboExpense['Id'];
                
                // Update local record
                $expense->update([
                    'qbo_expense_id' => $qboId,
                    'qbo_synced_at' => now(),
                ]);
                
                // Upload attachments to QBO
                $this->uploadAttachments($expense, $qboId, $token);
                
                return [
                    'success' => true,
                    'qbo_id' => $qboId,
                    'message' => "Expense for {$expense->asset->name} synced to QuickBooks"
                ];
            }
        }

        $error = $response->json()['Fault']['Error'][0]['Detail'] ?? 'Unknown error';
        return ['success' => false, 'qbo_id' => null, 'message' => $error];
    }

    /**
     * Update an existing Purchase (Expense) in QBO.
     */
    protected function updateExpense(AssetExpense $expense, QboToken $token): array
    {
        // First, get the current version from QBO
        $url = $this->baseUrl($token->realm_id) . "/purchase/{$expense->qbo_expense_id}";
        $response = Http::withHeaders($this->authHeaders())->get($url);

        if ($response->status() === 401) {
            $this->refreshTokenIfNeeded();
            $response = Http::withHeaders($this->authHeaders())->get($url);
        }

        if (!$response->successful()) {
            $error = $response->json()['Fault']['Error'][0]['Detail'] ?? 'Failed to fetch expense from QBO';
            return ['success' => false, 'qbo_id' => null, 'message' => $error];
        }

        $qboExpense = $response->json()['Purchase'];
        $syncToken = $qboExpense['SyncToken'];

        // Get expense account
        $accountRef = $this->getExpenseAccount($token, $expense->category);
        $description = $this->buildDescription($expense);

        // Update payload
        $payload = [
            'Id' => $expense->qbo_expense_id,
            'SyncToken' => $syncToken,
            'PaymentType' => $qboExpense['PaymentType'] ?? 'Cash',
            'AccountRef' => $accountRef,
            'Line' => [
                [
                    'Id' => $qboExpense['Line'][0]['Id'] ?? '1',
                    'DetailType' => 'AccountBasedExpenseLineDetail',
                    'Amount' => (float) $expense->amount,
                    'AccountBasedExpenseLineDetail' => [
                        'AccountRef' => $accountRef,
                    ],
                    'Description' => $description,
                ]
            ],
            'TotalAmt' => (float) $expense->amount,
            'PrivateNote' => $this->buildPrivateNote($expense),
            'DocNumber' => $expense->receipt_number,
            'TxnDate' => $expense->expense_date->format('Y-m-d'),
        ];

        // Add vendor if provided
        if ($expense->vendor) {
            $vendorRef = $this->ensureQboVendor($token, $expense->vendor);
            if ($vendorRef) {
                $payload['EntityRef'] = $vendorRef;
            }
        }

        $payload = $this->clean($payload);

        $updateUrl = $this->baseUrl($token->realm_id) . '/purchase?operation=update';
        $response = Http::withHeaders($this->authHeaders())
            ->withHeader('Content-Type', 'application/json')
            ->post($updateUrl, $payload);

        if (config('qbo.debug')) {
            Log::info('QBO Expense Update', [
                'expense_id' => $expense->id,
                'status' => $response->status(),
                'tid' => $response->header('intuit_tid'),
                'request' => $payload,
                'response' => $response->body(),
            ]);
        }

        if ($response->status() === 401) {
            $this->refreshTokenIfNeeded();
            $response = Http::withHeaders($this->authHeaders())
                ->withHeader('Content-Type', 'application/json')
                ->post($updateUrl, $payload);
        }

        if ($response->successful()) {
            $data = $response->json();
            $qboExpense = $data['Purchase'] ?? null;
            
            if ($qboExpense) {
                $expense->update([
                    'qbo_synced_at' => now(),
                ]);
                
                // Upload any new attachments
                $this->uploadAttachments($expense, $expense->qbo_expense_id, $token);
                
                return [
                    'success' => true,
                    'qbo_id' => $expense->qbo_expense_id,
                    'message' => "Expense for {$expense->asset->name} updated in QuickBooks"
                ];
            }
        }

        $error = $response->json()['Fault']['Error'][0]['Detail'] ?? 'Unknown error';
        return ['success' => false, 'qbo_id' => null, 'message' => $error];
    }

    /**
     * Upload attachments to QBO.
     */
    protected function uploadAttachments(AssetExpense $expense, string $qboExpenseId, QboToken $token): void
    {
        foreach ($expense->attachments as $attachment) {
            try {
                // Get file from storage
                $filePath = Storage::disk('public')->path($attachment->file_path);
                
                if (!file_exists($filePath)) {
                    Log::warning('Attachment file not found for QBO upload', [
                        'expense_id' => $expense->id,
                        'attachment_id' => $attachment->id,
                        'file_path' => $filePath,
                    ]);
                    continue;
                }

                // Read file content
                $fileContent = file_get_contents($filePath);
                $fileName = $attachment->file_name;

                // Upload to QBO
                $boundary = '-------------' . uniqid();
                $url = $this->baseUrl($token->realm_id) . '/upload';

                $body = "--{$boundary}\r\n";
                $body .= "Content-Disposition: form-data; name=\"file_metadata_01\"\r\n";
                $body .= "Content-Type: application/json\r\n\r\n";
                $body .= json_encode([
                    'AttachableRef' => [
                        [
                            'EntityRef' => [
                                'type' => 'Purchase',
                                'value' => $qboExpenseId,
                            ],
                        ],
                    ],
                    'FileName' => $fileName,
                    'Note' => $attachment->file_type,
                ]) . "\r\n";
                
                $body .= "--{$boundary}\r\n";
                $body .= "Content-Disposition: form-data; name=\"file_content_01\"; filename=\"{$fileName}\"\r\n";
                $body .= "Content-Type: {$attachment->file_type}\r\n\r\n";
                $body .= $fileContent . "\r\n";
                $body .= "--{$boundary}--\r\n";

                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $token->access_token,
                    'Accept' => 'application/json',
                    'Content-Type' => "multipart/form-data; boundary={$boundary}",
                ])
                ->withBody($body)
                ->post($url);

                if (config('qbo.debug')) {
                    Log::info('QBO Attachment Upload', [
                        'expense_id' => $expense->id,
                        'attachment_id' => $attachment->id,
                        'qbo_expense_id' => $qboExpenseId,
                        'status' => $response->status(),
                        'response' => $response->body(),
                    ]);
                }

            } catch (\Exception $e) {
                Log::error('QBO Attachment Upload Error', [
                    'expense_id' => $expense->id,
                    'attachment_id' => $attachment->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Build description for the expense line.
     */
    protected function buildDescription(AssetExpense $expense): string
    {
        $parts = [];
        
        $parts[] = "Asset: {$expense->asset->name}";
        
        if ($expense->asset->asset_tag) {
            $parts[] = "Tag: #{$expense->asset->asset_tag}";
        }
        
        $parts[] = "Category: " . ucfirst($expense->category);
        
        if ($expense->subcategory) {
            $parts[] = "Type: {$expense->subcategory}";
        }
        
        if ($expense->assetIssue) {
            $parts[] = "Issue: {$expense->assetIssue->title}";
        }
        
        if ($expense->odometer_hours) {
            $parts[] = "Odometer/Hours: {$expense->odometer_hours}";
        }
        
        if ($expense->description) {
            $parts[] = $expense->description;
        }
        
        return substr(implode(' | ', $parts), 0, 4000);
    }

    /**
     * Build private note for the expense.
     */
    protected function buildPrivateNote(AssetExpense $expense): string
    {
        $parts = [];
        
        if ($expense->notes) {
            $parts[] = $expense->notes;
        }
        
        if ($expense->is_reimbursable) {
            $parts[] = 'REIMBURSABLE';
        }
        
        $parts[] = "Submitted by: {$expense->submittedBy->name}";
        
        if ($expense->approvedBy) {
            $parts[] = "Approved by: {$expense->approvedBy->name}";
        }
        
        return substr(implode(' | ', $parts), 0, 4000);
    }

    /**
     * Get expense account reference based on category.
     */
    protected function getExpenseAccount(QboToken $token, string $category): array
    {
        // Map categories to QBO account names
        $accountMap = [
            'fuel' => 'Fuel',
            'repairs' => 'Repairs and Maintenance',
            'general' => 'General Expenses',
        ];

        $accountName = $accountMap[$category] ?? 'Other Expenses';

        // Try to find the account in QBO
        $url = $this->baseUrl($token->realm_id) . "/query?query=" . urlencode("SELECT * FROM Account WHERE Name = '{$accountName}' AND AccountType = 'Expense'");
        
        $response = Http::withHeaders($this->authHeaders())->get($url);

        if ($response->status() === 401) {
            $this->refreshTokenIfNeeded();
            $response = Http::withHeaders($this->authHeaders())->get($url);
        }

        if ($response->successful()) {
            $data = $response->json();
            $accounts = $data['QueryResponse']['Account'] ?? [];
            
            if (!empty($accounts)) {
                return ['value' => $accounts[0]['Id']];
            }
        }

        // If account not found, use a generic expense account (ID 7 is typically "Other Expenses" in QBO)
        // In production, you might want to create the account if it doesn't exist
        return ['value' => '7'];
    }

    /**
     * Ensure vendor exists in QBO or create it.
     */
    protected function ensureQboVendor(QboToken $token, string $vendorName): ?array
    {
        // Try to find vendor
        $url = $this->baseUrl($token->realm_id) . "/query?query=" . urlencode("SELECT * FROM Vendor WHERE DisplayName = '{$vendorName}'");
        
        $response = Http::withHeaders($this->authHeaders())->get($url);

        if ($response->status() === 401) {
            $this->refreshTokenIfNeeded();
            $response = Http::withHeaders($this->authHeaders())->get($url);
        }

        if ($response->successful()) {
            $data = $response->json();
            $vendors = $data['QueryResponse']['Vendor'] ?? [];
            
            if (!empty($vendors)) {
                return ['value' => $vendors[0]['Id']];
            }
        }

        // Vendor not found, create it
        $createUrl = $this->baseUrl($token->realm_id) . '/vendor';
        $payload = [
            'DisplayName' => substr($vendorName, 0, 100),
        ];

        $response = Http::withHeaders($this->authHeaders())
            ->withHeader('Content-Type', 'application/json')
            ->post($createUrl, $payload);

        if ($response->status() === 401) {
            $this->refreshTokenIfNeeded();
            $response = Http::withHeaders($this->authHeaders())
                ->withHeader('Content-Type', 'application/json')
                ->post($createUrl, $payload);
        }

        if ($response->successful()) {
            $data = $response->json();
            $vendor = $data['Vendor'] ?? null;
            
            if ($vendor) {
                return ['value' => $vendor['Id']];
            }
        }

        return null;
    }

    /**
     * Delete an expense from QBO.
     */
    public function deleteExpense(AssetExpense $expense): array
    {
        $token = QboToken::latest('updated_at')->first();
        if (!$token) {
            return ['success' => false, 'message' => 'QBO not connected'];
        }

        if (!$expense->qbo_expense_id) {
            return ['success' => false, 'message' => 'Expense not synced to QBO'];
        }

        try {
            // Get current version
            $url = $this->baseUrl($token->realm_id) . "/purchase/{$expense->qbo_expense_id}";
            $response = Http::withHeaders($this->authHeaders())->get($url);

            if ($response->status() === 401) {
                $this->refreshTokenIfNeeded();
                $response = Http::withHeaders($this->authHeaders())->get($url);
            }

            if (!$response->successful()) {
                return ['success' => false, 'message' => 'Failed to fetch expense from QBO'];
            }

            $qboExpense = $response->json()['Purchase'];
            $syncToken = $qboExpense['SyncToken'];

            // Delete using operation=delete
            $deleteUrl = $this->baseUrl($token->realm_id) . '/purchase?operation=delete';
            $payload = [
                'Id' => $expense->qbo_expense_id,
                'SyncToken' => $syncToken,
            ];

            $response = Http::withHeaders($this->authHeaders())
                ->withHeader('Content-Type', 'application/json')
                ->post($deleteUrl, $payload);

            if ($response->status() === 401) {
                $this->refreshTokenIfNeeded();
                $response = Http::withHeaders($this->authHeaders())
                    ->withHeader('Content-Type', 'application/json')
                    ->post($deleteUrl, $payload);
            }

            if ($response->successful()) {
                return [
                    'success' => true,
                    'message' => "Expense deleted from QuickBooks"
                ];
            }

            $error = $response->json()['Fault']['Error'][0]['Detail'] ?? 'Unknown error';
            return ['success' => false, 'message' => $error];

        } catch (\Exception $e) {
            if (config('qbo.debug')) {
                Log::error('QBO Expense Delete Error', [
                    'expense_id' => $expense->id,
                    'error' => $e->getMessage(),
                ]);
            }
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}
