<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\QboToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ContactQboImportController extends Controller
{
    const PAGE_SIZE = 100; // QBO max page size

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
        // if expires_at is in the past or within 60 seconds, refresh
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
            // Company record: keep company_name, use given/family when present
            return [
                'first' => $first !== '' ? $first : null,
                'last' => $last !== '' ? $last : null,
                'company' => $company,
            ];
        }

        // Person record (no CompanyName)
        if ($first !== '' || $last !== '') {
            return [
                'first' => $first !== '' ? $first : null,
                'last' => $last !== '' ? $last : null,
                'company' => null,
            ];
        }

        // Try to infer from DisplayName without assigning it to company
        if ($display !== '') {
            if (str_contains($display, ',')) {
                // Format: Last, First
                [$l,$f] = array_map('trim', explode(',', $display, 2));
                return ['first' => $f ?: null, 'last' => $l ?: null, 'company' => null];
            }
            $parts = preg_split('/\s+/', $display);
            if (count($parts) >= 2) {
                $f = array_shift($parts);
                $l = implode(' ', $parts);
                return ['first' => $f ?: null, 'last' => $l ?: null, 'company' => null];
            }
            return ['first' => $display, 'last' => null, 'company' => null];
        }

        // Fallback
        return ['first' => 'Customer', 'last' => (string) ($c['Id'] ?? ''), 'company' => null];
    }

    public function search(Request $request)
    {
        $token = QboToken::latest('updated_at')->first();
        if (!$token) return redirect()->route('integrations.qbo.settings')->with('error', 'Connect QuickBooks first');

        $q = trim($request->get('q', ''));
        $results = [];
        $start = max(1, (int) $request->get('start', 1));
        $max = min(100, max(1, (int) $request->get('max', 25)));
        $error = null;

        $doFetch = $request->boolean('list') || $q !== '';

        if ($doFetch) {
            $fieldsFull = 'Id, DisplayName, CompanyName, GivenName, FamilyName, PrimaryEmailAddr, PrimaryPhone, BillAddr';
            $fieldsLite = 'Id, DisplayName, CompanyName, GivenName, FamilyName, PrimaryEmailAddr, PrimaryPhone';
            if ($q !== '') {
                // Fallback: keep it simple — search by DisplayName only to avoid QBO parser quirks
                $term = str_replace("'", "''", $q);
                $queryFull = "SELECT {$fieldsFull} FROM Customer WHERE DisplayName LIKE '%{$term}%' ORDER BY DisplayName STARTPOSITION {$start} MAXRESULTS {$max}";
                $queryLite = "SELECT {$fieldsLite} FROM Customer WHERE DisplayName LIKE '%{$term}%' ORDER BY DisplayName STARTPOSITION {$start} MAXRESULTS {$max}";
            } else {
                // List mode: no WHERE, just page through all customers
                $queryFull = "SELECT {$fieldsFull} FROM Customer ORDER BY DisplayName STARTPOSITION {$start} MAXRESULTS {$max}";
                $queryLite = "SELECT {$fieldsLite} FROM Customer ORDER BY DisplayName STARTPOSITION {$start} MAXRESULTS {$max}";
            }

            // First attempt
            $res = Http::withHeaders($this->authHeaders())
                ->get($this->baseUrl($token->realm_id).'/query', [ 'query' => $queryFull, 'minorversion' => 65 ]);

            // If token expired, try refresh once and retry
            if ($res->status() === 401 || str_contains($res->body(), 'Token expired')) {
                $this->refreshTokenIfNeeded();
                $res = Http::withHeaders($this->authHeaders())
                    ->get($this->baseUrl($token->realm_id).'/query', [ 'query' => $queryFull, 'minorversion' => 65 ]);
            }

            if (!$res->ok()) {
                // If invalid query due to field selection (e.g., BillAddr not supported), retry with a lite field list
                $body = $res->body();
                $res = Http::withHeaders($this->authHeaders())
                    ->get($this->baseUrl($token->realm_id).'/query', [ 'query' => $queryLite, 'minorversion' => 65 ]);
                if (!$res->ok()) {
                    $error = 'QBO search failed: '.$body; // report first error which explains the query issue
                }
            }

            if ($res->ok()) {
                $customers = $res->json()['QueryResponse']['Customer'] ?? [];
                $results = is_array($customers) ? $customers : [];
            } else {
                // Do not redirect back to Contacts — render the import page with an inline error
                $results = [];
                if (!$error) $error = 'QBO search failed: '.$res->body();
            }
        }

        $hasMore = count($results) === $max;
        $prevStart = $start > 1 ? max(1, $start - $max) : null;
        $nextStart = $hasMore ? $start + $max : null;

        return view('contacts.qbo-import', [
            'results' => $results,
            'start' => $start,
            'max' => $max,
            'prevStart' => $prevStart,
            'nextStart' => $nextStart,
            'error' => $error,
        ]);
    }

    public function import(Request $request)
    {
        $request->validate(['qbo_customer_id' => 'required']);
        $token = QboToken::latest('updated_at')->first();
        if (!$token) return redirect()->route('integrations.qbo.settings')->with('error', 'Connect QuickBooks first');

        $id = $request->input('qbo_customer_id');
        $url = $this->baseUrl($token->realm_id).'/customer/'.$id;
        $res = Http::withHeaders($this->authHeaders())->get($url, ['minorversion' => 65]);
        if ($res->status() === 401 || str_contains($res->body(), 'Token expired')) {
            $this->refreshTokenIfNeeded();
            $res = Http::withHeaders($this->authHeaders())->get($url, ['minorversion' => 65]);
        }
        if (!$res->ok()) return back()->with('error', 'QBO fetch failed: '.$res->body());
        $j = $res->json();
        $c = is_array($j) ? ($j['Customer'] ?? []) : [];
        if (!$c || empty($c['Id'])) {
            return back()->with('error', 'QBO fetch failed: No customer found in response');
        }

        $addr = $c['BillAddr'] ?? [];
        $email = $c['PrimaryEmailAddr']['Address'] ?? null;
        $phone = $c['PrimaryPhone']['FreeFormNumber'] ?? null;
        $mobile = $c['Mobile']['FreeFormNumber'] ?? null;

        $names = $this->mapNames($c);

        try {
            $contact = Contact::firstOrCreate(
                ['qbo_customer_id' => $c['Id']],
                [
                    'first_name' => $names['first'],
                    'last_name' => $names['last'],
                    'company_name' => $names['company'],
                    'contact_type' => 'client',
                    'email' => $email,
                    'phone' => $phone,
                    'mobile' => $mobile,
                    'address' => $addr['Line1'] ?? null,
                    'city' => $addr['City'] ?? null,
                    'state' => $addr['CountrySubDivisionCode'] ?? null,
                    'postal_code' => $addr['PostalCode'] ?? null,
                ]
            );

            $contact->qbo_sync_token = $c['SyncToken'] ?? null;
            $contact->qbo_last_synced_at = now();
            $contact->save();
        } catch (\Throwable $e) {
            return back()->with('error', 'Import failed: '.$e->getMessage());
        }

        return redirect()->route('contacts.index')->with('success', 'Imported from QuickBooks');
    }

    public function importSelected(Request $request)
    {
        $ids = $request->input('ids', []);
        if (!is_array($ids) || empty($ids)) return back()->with('error', 'No customers selected');
        $token = QboToken::latest('updated_at')->first();
        if (!$token) return redirect()->route('integrations.qbo.settings')->with('error', 'Connect QuickBooks first');

        $imported = 0; $errors = 0;
        foreach ($ids as $id) {
            try {
                $url = $this->baseUrl($token->realm_id).'/customer/'.$id;
                $res = Http::withHeaders($this->authHeaders())->get($url, ['minorversion' => 65]);
                if ($res->status() === 401 || str_contains($res->body(), 'Token expired')) {
                    $this->refreshTokenIfNeeded();
                    $res = Http::withHeaders($this->authHeaders())->get($url, ['minorversion' => 65]);
                }
                if (!$res->ok()) { $errors++; continue; }
                $c = $res->json()['Customer'] ?? [];
                if (!$c) { $errors++; continue; }

                $addr = $c['BillAddr'] ?? [];
                $email = $c['PrimaryEmailAddr']['Address'] ?? null;
                $phone = $c['PrimaryPhone']['FreeFormNumber'] ?? null;
                $mobile = $c['Mobile']['FreeFormNumber'] ?? null;
                $names = $this->mapNames($c);

                $contact = Contact::firstOrCreate(
                    ['qbo_customer_id' => $c['Id']],
                    [
                        'first_name' => $names['first'],
                        'last_name' => $names['last'],
                        'company_name' => $names['company'],
                        'contact_type' => 'client',
                        'email' => $email,
                        'phone' => $phone,
                        'mobile' => $mobile,
                        'address' => $addr['Line1'] ?? null,
                        'city' => $addr['City'] ?? null,
                        'state' => $addr['CountrySubDivisionCode'] ?? null,
                        'postal_code' => $addr['PostalCode'] ?? null,
                    ]
                );
                $contact->qbo_sync_token = $c['SyncToken'] ?? null;
                $contact->qbo_last_synced_at = now();
                $contact->save();
                $imported++;
            } catch (\Throwable $e) { $errors++; }
        }

        return back()->with('success', "Imported selected: {$imported}. Errors: {$errors}.");
    }

    public function importBulk(Request $request)
    {
        $token = QboToken::latest('updated_at')->first();
        if (!$token) return redirect()->route('integrations.qbo.settings')->with('error', 'Connect QuickBooks first');

        $max = self::PAGE_SIZE;
        $start = 1;
        $imported = 0; $skipped = 0; $errors = 0;
        $errorMsg = null;

        while (true) {
            $fieldsLite = 'Id, DisplayName, CompanyName, GivenName, FamilyName, PrimaryEmailAddr, PrimaryPhone';
            $query = "SELECT {$fieldsLite} FROM Customer ORDER BY DisplayName STARTPOSITION {$start} MAXRESULTS {$max}";
            $res = Http::withHeaders($this->authHeaders())
                ->get($this->baseUrl($token->realm_id).'/query', [ 'query' => $query, 'minorversion' => 65 ]);
            if ($res->status() === 401 || str_contains($res->body(), 'Token expired')) {
                $this->refreshTokenIfNeeded();
                $res = Http::withHeaders($this->authHeaders())
                    ->get($this->baseUrl($token->realm_id).'/query', [ 'query' => $query, 'minorversion' => 65 ]);
            }
            if (!$res->ok()) {
                $errorMsg = 'QBO bulk list failed: '.$res->body();
                break;
            }
            $customers = $res->json()['QueryResponse']['Customer'] ?? [];
            $customers = is_array($customers) ? $customers : [];
            if (empty($customers)) break;

            foreach ($customers as $row) {
                try {
                    $id = $row['Id'] ?? null;
                    if (!$id) { $skipped++; continue; }
                    // Fetch full customer to get BillAddr and SyncToken
                    $url = $this->baseUrl($token->realm_id).'/customer/'.$id;
                    $get = Http::withHeaders($this->authHeaders())->get($url, ['minorversion' => 65]);
                    if ($get->status() === 401 || str_contains($get->body(), 'Token expired')) {
                        $this->refreshTokenIfNeeded();
                        $get = Http::withHeaders($this->authHeaders())->get($url, ['minorversion' => 65]);
                    }
                    if (!$get->ok()) { $errors++; continue; }
                    $c = $get->json()['Customer'] ?? [];
                    if (!$c) { $skipped++; continue; }

                    $addr = $c['BillAddr'] ?? [];
                    $email = $c['PrimaryEmailAddr']['Address'] ?? null;
                    $phone = $c['PrimaryPhone']['FreeFormNumber'] ?? null;
                    $mobile = $c['Mobile']['FreeFormNumber'] ?? null;

                    $names = $this->mapNames($c);

                    $contact = Contact::firstOrCreate(
                        ['qbo_customer_id' => $c['Id']],
                        [
                            'first_name' => $names['first'],
                            'last_name' => $names['last'],
                            'company_name' => $names['company'],
                            'contact_type' => 'client',
                            'email' => $email,
                            'phone' => $phone,
                            'mobile' => $mobile,
                            'address' => $addr['Line1'] ?? null,
                            'city' => $addr['City'] ?? null,
                            'state' => $addr['CountrySubDivisionCode'] ?? null,
                            'postal_code' => $addr['PostalCode'] ?? null,
                        ]
                    );
                    $contact->qbo_sync_token = $c['SyncToken'] ?? null;
                    $contact->qbo_last_synced_at = now();
                    $contact->save();

                    $imported++;
                } catch (\Throwable $e) {
                    $errors++;
                }
            }

            if (count($customers) < $max) break; // last page
            $start += $max;
        }

        $msg = "Imported: {$imported}. Skipped: {$skipped}. Errors: {$errors}.";
        if ($errorMsg) $msg .= ' Note: '.$errorMsg;
        return redirect()->route('contacts.index')->with('success', $msg);
    }
}
