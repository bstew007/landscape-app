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

    public function search(Request $request)
    {
        $token = QboToken::latest('updated_at')->first();
        if (!$token) return redirect()->route('integrations.qbo.settings')->with('error', 'Connect QuickBooks first');

        $q = trim($request->get('q', ''));
        $results = [];
        $start = max(1, (int) $request->get('start', 1));
        $max = min(100, max(1, (int) $request->get('max', 25)));

        if ($q !== '') {
            // Fallback: keep it simple — search by DisplayName only to avoid QBO parser quirks
            $term = str_replace("'", "''", $q);
            $fields = 'Id, DisplayName, CompanyName, GivenName, FamilyName, PrimaryEmailAddr, PrimaryPhone, BillAddr';
            $query = "SELECT {$fields} FROM Customer WHERE DisplayName LIKE '%{$term}%' ORDERBY DisplayName STARTPOSITION {$start} MAXRESULTS {$max}";
            $res = Http::withHeaders($this->authHeaders())
                ->get($this->baseUrl($token->realm_id).'/query', [ 'query' => $query, 'minorversion' => 65 ]);
            if ($res->ok()) {
                $customers = $res->json()['QueryResponse']['Customer'] ?? [];
                $results = is_array($customers) ? $customers : [];
            } else {
                return back()->with('error', 'QBO search failed: '.$res->body());
            }
        } else {
            // List mode: no WHERE, just page through all customers
            $fields = 'Id, DisplayName, CompanyName, GivenName, FamilyName, PrimaryEmailAddr, PrimaryPhone, BillAddr';
            $query = "SELECT {$fields} FROM Customer ORDERBY DisplayName STARTPOSITION {$start} MAXRESULTS {$max}";
            $res = Http::withHeaders($this->authHeaders())
                ->get($this->baseUrl($token->realm_id).'/query', [ 'query' => $query, 'minorversion' => 65 ]);
            if ($res->ok()) {
                $customers = $res->json()['QueryResponse']['Customer'] ?? [];
                $results = is_array($customers) ? $customers : [];
            } else {
                return back()->with('error', 'QBO search failed: '.$res->body());
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
        ]);
        return view('contacts.qbo-import', compact('results'));
    }

    public function import(Request $request)
    {
        $request->validate(['qbo_customer_id' => 'required']);
        $token = QboToken::latest('updated_at')->first();
        if (!$token) return redirect()->route('integrations.qbo.settings')->with('error', 'Connect QuickBooks first');

        $id = $request->input('qbo_customer_id');
        $res = Http::withHeaders($this->authHeaders())->get($this->baseUrl($token->realm_id).'/customer/'.$id);
        if (!$res->ok()) return back()->with('error', 'QBO fetch failed: '.$res->body());
        $c = $res->json()['Customer'] ?? [];

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
