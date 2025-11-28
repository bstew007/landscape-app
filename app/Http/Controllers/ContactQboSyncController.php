<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\QboToken;
use App\Services\QboCustomerService;
use App\Services\QboVendorService;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ContactQboSyncController extends Controller
{
    // ================================
    // Customer Sync Methods
    // ================================
    
    public function sync(Contact $client, QboCustomerService $svc)
    {
        try {
            $svc->upsert($client);
            return back()->with('success', 'Synced to QuickBooks as Customer');
        } catch (\Throwable $e) {
            return back()->with('error', 'QBO Customer sync failed: '.$e->getMessage());
        }
    }

    public function pushNames(Contact $client, QboCustomerService $svc)
    {
        try {
            $svc->updateNames($client);
            return back()->with('success', 'Names updated in QuickBooks');
        } catch (\Throwable $e) {
            return back()->with('error', 'QBO name update failed: '.$e->getMessage());
        }
    }

    public function pushMobile(Contact $client, QboCustomerService $svc)
    {
        try {
            $svc->updateMobile($client);
            return back()->with('success', 'Mobile updated in QuickBooks');
        } catch (\Throwable $e) {
            return back()->with('error', 'QBO mobile update failed: '.$e->getMessage());
        }
    }

    // Refresh local contact from QBO (inbound)
    public function refresh(Contact $client)
    {
        try {
            if (!$client->qbo_customer_id) {
                return back()->with('error', 'This contact is not linked to QBO as a Customer.');
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

            return back()->with('success', 'Refreshed from QuickBooks Customer.');
        } catch (\Throwable $e) {
            return back()->with('error', 'QBO refresh failed: '.$e->getMessage());
        }
    }

    // ================================
    // Vendor Sync Methods
    // ================================
    
    public function syncVendor(Contact $client, QboVendorService $svc, Request $request)
    {
        try {
            \Log::info('Syncing vendor to QB', ['vendor_id' => $client->id, 'qbo_vendor_id' => $client->qbo_vendor_id]);
            $result = $svc->upsert($client);
            \Log::info('Vendor sync successful', ['vendor_id' => $client->id, 'result' => $result]);
            
            // Check if any fields were skipped
            $message = 'Synced to QuickBooks as Vendor';
            if (isset($result['skipped'])) {
                if ($result['skipped'] === 'no_changes') {
                    $message = 'Vendor is already up to date in QuickBooks';
                } elseif ($result['skipped'] === 'excluded_fields_changed') {
                    $message = 'Vendor synced (Note: Name/Mobile changes require manual update in QuickBooks)';
                }
            }
            
            if ($request->wantsJson()) {
                return response()->json(['success' => true, 'message' => $message]);
            }
            
            return redirect()->back()->with('success', $message);
        } catch (\Throwable $e) {
            \Log::error('Vendor sync failed', [
                'vendor_id' => $client->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
            }
            
            return redirect()->back()->with('error', 'QBO Vendor sync failed: '.$e->getMessage());
        }
    }

    public function pushVendorNames(Contact $client, QboVendorService $svc)
    {
        try {
            $svc->updateNames($client);
            return back()->with('success', 'Vendor names updated in QuickBooks');
        } catch (\Throwable $e) {
            return back()->with('error', 'QBO Vendor name update failed: '.$e->getMessage());
        }
    }

    public function pushVendorMobile(Contact $client, QboVendorService $svc)
    {
        try {
            $svc->updateMobile($client);
            return back()->with('success', 'Vendor mobile updated in QuickBooks');
        } catch (\Throwable $e) {
            return back()->with('error', 'QBO Vendor mobile update failed: '.$e->getMessage());
        }
    }

    // Refresh local contact from QBO Vendor (inbound)
    public function refreshVendor(Contact $client, QboVendorService $svc)
    {
        try {
            if (!$client->qbo_vendor_id) {
                return back()->with('error', 'This contact is not linked to QBO as a Vendor.');
            }
            
            $data = $svc->fetch($client);
            $v = $data['Vendor'] ?? [];
            if (!$v) return back()->with('error', 'QBO vendor not found.');

            $addr = $v['BillAddr'] ?? [];
            $email = $v['PrimaryEmailAddr']['Address'] ?? null;
            $phone = $v['PrimaryPhone']['FreeFormNumber'] ?? null;
            $mobile = $v['Mobile']['FreeFormNumber'] ?? null;
            $names = $this->mapVendorNames($v);
            if (empty($names['first'])) $names['first'] = 'Vendor';
            if (!array_key_exists('last', $names) || $names['last'] === null) $names['last'] = (string) ($v['Id'] ?? '');

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
            $client->qbo_sync_token = $v['SyncToken'] ?? $client->qbo_sync_token;
            $client->qbo_last_synced_at = now();
            $client->save();

            return back()->with('success', 'Refreshed from QuickBooks Vendor.');
        } catch (\Throwable $e) {
            return back()->with('error', 'QBO Vendor refresh failed: '.$e->getMessage());
        }
    }

    // ================================
    // Helper Methods
    // ================================

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

    protected function mapVendorNames(array $v): array
    {
        $display = trim($v['DisplayName'] ?? '');
        $company = trim($v['CompanyName'] ?? '');
        $first = trim($v['GivenName'] ?? '');
        $last = trim($v['FamilyName'] ?? '');
        if ($company !== '') {
            $outFirst = $first !== '' ? $first : ($display !== '' ? $display : 'Vendor');
            $outLast = $last !== '' ? $last : ((string) ($v['Id'] ?? ''));
            return ['first' => $outFirst, 'last' => $outLast, 'company' => $company];
        }
        if ($first !== '' || $last !== '') return ['first' => $first ?: 'Vendor', 'last' => $last ?: ((string) ($v['Id'] ?? '')), 'company' => null];
        if ($display !== '') {
            if (str_contains($display, ',')) { [$l,$f] = array_map('trim', explode(',', $display, 2)); return ['first'=>$f?:'Vendor','last'=>$l?:((string) ($v['Id'] ?? '')),'company'=>null]; }
            $parts = preg_split('/\s+/', $display); if (count($parts)>=2){ $f=array_shift($parts); $l=implode(' ', $parts); return ['first'=>$f?:'Vendor','last'=>$l?:((string) ($v['Id'] ?? '')),'company'=>null]; }
            return ['first'=>$display,'last'=>((string) ($v['Id'] ?? '')),'company'=>null];
        }
        return ['first'=>'Vendor','last'=>(string)($v['Id']??''),'company'=>null];
    }
}
