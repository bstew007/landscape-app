<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\QboToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ContactQboVendorImportController extends Controller
{
    public function linkPage()
    {
        $token = QboToken::latest('updated_at')->first();
        if (!$token) {
            return redirect()->route('contacts.index')
                ->with('error', 'QuickBooks not connected. Please connect to QuickBooks first.');
        }

        // Get all local vendors
        $vendors = Contact::where('contact_type', 'vendor')
            ->withCount(['materials' => function($q) {
                $q->where('supplier_id', '!=', null);
            }])
            ->orderByRaw('qbo_vendor_id IS NULL DESC')
            ->orderBy('company_name')
            ->paginate(50);

        $unlinkedCount = Contact::where('contact_type', 'vendor')
            ->whereNull('qbo_vendor_id')
            ->count();

        // Fetch ALL QB vendors for dropdown
        $qboVendors = $this->fetchAllQboVendors($token);
        
        // Get list of already linked QB vendor IDs
        $linkedQboIds = Contact::whereNotNull('qbo_vendor_id')
            ->pluck('qbo_vendor_id')
            ->toArray();

        return view('contacts.vendor-qbo-link', compact('vendors', 'qboVendors', 'linkedQboIds', 'unlinkedCount'));
    }

    protected function fetchAllQboVendors(QboToken $token): array
    {
        $allVendors = [];
        $start = 1;
        $maxResults = 100;
        
        try {
            do {
                $sql = "SELECT * FROM Vendor STARTPOSITION {$start} MAXRESULTS {$maxResults}";
                
                $res = Http::withHeaders($this->authHeaders($token))
                    ->get($this->baseUrl($token->realm_id).'/query', ['query' => $sql, 'minorversion' => 65]);
                
                if ($res->status() === 401) {
                    $this->refreshTokenIfNeeded($token);
                    $res = Http::withHeaders($this->authHeaders($token))
                        ->get($this->baseUrl($token->realm_id).'/query', ['query' => $sql, 'minorversion' => 65]);
                }

                if ($res->ok()) {
                    $data = $res->json();
                    $vendors = $data['QueryResponse']['Vendor'] ?? [];
                    $allVendors = array_merge($allVendors, $vendors);
                    
                    // Check if there are more results
                    if (count($vendors) < $maxResults) {
                        break;
                    }
                    $start += $maxResults;
                } else {
                    break;
                }
            } while (true);
            
        } catch (\Throwable $e) {
            \Log::error('Failed to fetch QB vendors: ' . $e->getMessage());
        }

        return $allVendors;
    }

    public function syncAll()
    {
        $vendors = Contact::where('contact_type', 'vendor')
            ->whereNull('qbo_vendor_id')
            ->get();

        $service = app(\App\Services\QboVendorService::class);
        $synced = 0;
        $failed = 0;

        foreach ($vendors as $vendor) {
            try {
                $service->upsert($vendor);
                $synced++;
            } catch (\Throwable $e) {
                $failed++;
                \Log::error("Failed to sync vendor {$vendor->id}: " . $e->getMessage());
            }
        }

        $message = "Synced {$synced} vendor" . ($synced !== 1 ? 's' : '') . " to QuickBooks.";
        if ($failed > 0) {
            $message .= " {$failed} failed.";
        }

        return back()->with('success', $message);
    }

    public function search(Request $request)
    {
        $token = QboToken::latest('updated_at')->first();
        if (!$token) {
            return view('contacts.qbo-vendor-import', [
                'error' => 'QuickBooks not connected. Please connect to QuickBooks first.',
                'vendors' => [],
            ]);
        }

        $query = $request->input('q', '');
        $start = (int) $request->input('start', 1);
        $max = (int) $request->input('max', 25);

        $vendors = [];
        $count = 0;

        if ($query || $request->has('fetch_all')) {
            try {
                $sql = $query 
                    ? "SELECT * FROM Vendor WHERE DisplayName LIKE '%{$query}%' OR CompanyName LIKE '%{$query}%' STARTPOSITION {$start} MAXRESULTS {$max}"
                    : "SELECT * FROM Vendor STARTPOSITION {$start} MAXRESULTS {$max}";
                
                $res = Http::withHeaders($this->authHeaders($token))
                    ->get($this->baseUrl($token->realm_id).'/query', ['query' => $sql, 'minorversion' => 65]);
                
                if ($res->status() === 401) {
                    $this->refreshTokenIfNeeded($token);
                    $res = Http::withHeaders($this->authHeaders($token))
                        ->get($this->baseUrl($token->realm_id).'/query', ['query' => $sql, 'minorversion' => 65]);
                }

                if ($res->ok()) {
                    $data = $res->json();
                    $vendors = $data['QueryResponse']['Vendor'] ?? [];
                    $count = $data['QueryResponse']['totalCount'] ?? count($vendors);
                }
            } catch (\Throwable $e) {
                return view('contacts.qbo-vendor-import', [
                    'error' => 'QuickBooks query failed: ' . $e->getMessage(),
                    'vendors' => [],
                    'query' => $query,
                ]);
            }
        }

        // Check which vendors are already linked
        $linkedIds = Contact::whereNotNull('qbo_vendor_id')->pluck('qbo_vendor_id')->toArray();
        
        foreach ($vendors as &$vendor) {
            $vendor['is_linked'] = in_array($vendor['Id'], $linkedIds);
            $vendor['local_contact'] = Contact::where('qbo_vendor_id', $vendor['Id'])->first();
        }

        return view('contacts.qbo-vendor-import', [
            'vendors' => $vendors,
            'query' => $query,
            'start' => $start,
            'max' => $max,
            'count' => $count,
        ]);
    }

    public function import(Request $request)
    {
        $token = QboToken::latest('updated_at')->first();
        if (!$token) {
            return back()->with('error', 'QuickBooks not connected.');
        }

        $qboVendorId = $request->input('qbo_vendor_id');
        if (!$qboVendorId) {
            return back()->with('error', 'No vendor ID provided.');
        }

        try {
            $res = Http::withHeaders($this->authHeaders($token))
                ->get($this->baseUrl($token->realm_id).'/vendor/'.$qboVendorId, ['minorversion' => 65]);
            
            if ($res->status() === 401) {
                $this->refreshTokenIfNeeded($token);
                $res = Http::withHeaders($this->authHeaders($token))
                    ->get($this->baseUrl($token->realm_id).'/vendor/'.$qboVendorId, ['minorversion' => 65]);
            }

            if (!$res->ok()) {
                return back()->with('error', 'Failed to fetch vendor from QuickBooks.');
            }

            $v = $res->json()['Vendor'] ?? null;
            if (!$v) {
                return back()->with('error', 'Vendor not found in QuickBooks.');
            }

            // Check if already linked
            $existing = Contact::where('qbo_vendor_id', $qboVendorId)->first();
            if ($existing) {
                return back()->with('error', "Vendor already linked to contact: {$existing->name}");
            }

            // Create new contact from QB vendor
            $addr = $v['BillAddr'] ?? [];
            $names = $this->mapNames($v);
            
            $contact = Contact::create([
                'first_name' => $names['first'],
                'last_name' => $names['last'],
                'company_name' => $names['company'] ?? null,
                'contact_type' => 'vendor',
                'email' => $v['PrimaryEmailAddr']['Address'] ?? null,
                'phone' => $v['PrimaryPhone']['FreeFormNumber'] ?? null,
                'mobile' => $v['Mobile']['FreeFormNumber'] ?? null,
                'address' => $addr['Line1'] ?? null,
                'city' => $addr['City'] ?? null,
                'state' => $addr['CountrySubDivisionCode'] ?? null,
                'postal_code' => $addr['PostalCode'] ?? null,
                'qbo_vendor_id' => $v['Id'],
                'qbo_sync_token' => $v['SyncToken'] ?? null,
                'qbo_last_synced_at' => now(),
            ]);

            return redirect()->route('contacts.qbo.vendor.search')
                ->with('success', "Vendor imported: {$contact->name}");

        } catch (\Throwable $e) {
            return back()->with('error', 'Import failed: ' . $e->getMessage());
        }
    }

    public function link(Contact $client, Request $request)
    {
        $qboVendorId = $request->input('qbo_vendor_id');
        if (!$qboVendorId) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'No QuickBooks vendor ID provided'], 400);
            }
            return back()->with('error', 'No QuickBooks vendor ID provided.');
        }

        // Check if this QB vendor is already linked to another contact
        $existing = Contact::where('qbo_vendor_id', $qboVendorId)
            ->where('id', '!=', $client->id)
            ->first();
        
        if ($existing) {
            $message = "This QuickBooks vendor is already linked to: {$existing->name}";
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $message], 400);
            }
            return back()->with('error', $message);
        }

        $client->qbo_vendor_id = $qboVendorId;
        $client->qbo_last_synced_at = now();
        $client->save();

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Vendor linked to QuickBooks']);
        }
        
        return back()->with('success', 'Contact linked to QuickBooks vendor.');
    }

    protected function baseUrl(string $realmId): string
    {
        $env = config('qbo.environment');
        $host = $env === 'production' ? 'quickbooks.api.intuit.com' : 'sandbox-quickbooks.api.intuit.com';
        return "https://{$host}/v3/company/{$realmId}";
    }

    protected function authHeaders(QboToken $token): array
    {
        return [
            'Authorization' => 'Bearer ' . $token->access_token,
            'Accept' => 'application/json',
        ];
    }

    protected function refreshTokenIfNeeded(QboToken $token): void
    {
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

    protected function mapNames(array $v): array
    {
        $display = trim($v['DisplayName'] ?? '');
        $company = trim($v['CompanyName'] ?? '');
        $first = trim($v['GivenName'] ?? '');
        $last = trim($v['FamilyName'] ?? '');
        
        if ($company !== '') {
            return [
                'first' => $first ?: ($display ?: 'Vendor'),
                'last' => $last ?: (string)($v['Id'] ?? ''),
                'company' => $company,
            ];
        }
        
        if ($first !== '' || $last !== '') {
            return [
                'first' => $first ?: 'Vendor',
                'last' => $last ?: (string)($v['Id'] ?? ''),
                'company' => null,
            ];
        }
        
        if ($display !== '') {
            if (str_contains($display, ',')) {
                [$l, $f] = array_map('trim', explode(',', $display, 2));
                return ['first' => $f ?: 'Vendor', 'last' => $l ?: (string)($v['Id'] ?? ''), 'company' => null];
            }
            
            $parts = preg_split('/\s+/', $display);
            if (count($parts) >= 2) {
                $f = array_shift($parts);
                $l = implode(' ', $parts);
                return ['first' => $f ?: 'Vendor', 'last' => $l ?: (string)($v['Id'] ?? ''), 'company' => null];
            }
            
            return ['first' => $display, 'last' => (string)($v['Id'] ?? ''), 'company' => null];
        }
        
        return ['first' => 'Vendor', 'last' => (string)($v['Id'] ?? ''), 'company' => null];
    }
}
