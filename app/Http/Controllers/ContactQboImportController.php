<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\QboToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ContactQboImportController extends Controller
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

        $addr = $c['BillAddr'] ?? [];
        $email = $c['PrimaryEmailAddr']['Address'] ?? null;
        $phone = $c['PrimaryPhone']['FreeFormNumber'] ?? null;

        $contact = Contact::firstOrCreate(
            ['qbo_customer_id' => $c['Id']],
            [
                'first_name' => $c['GivenName'] ?? null,
                'last_name' => $c['FamilyName'] ?? null,
                'company_name' => $c['CompanyName'] ?? ($c['DisplayName'] ?? null),
                'contact_type' => 'client',
                'email' => $email,
                'phone' => $phone,
                'address' => $addr['Line1'] ?? null,
                'city' => $addr['City'] ?? null,
                'state' => $addr['CountrySubDivisionCode'] ?? null,
                'postal_code' => $addr['PostalCode'] ?? null,
            ]
        );

        $contact->qbo_sync_token = $c['SyncToken'] ?? null;
        $contact->qbo_last_synced_at = now();
        $contact->save();

        return redirect()->route('contacts.index')->with('success', 'Imported from QuickBooks');
    }
}
