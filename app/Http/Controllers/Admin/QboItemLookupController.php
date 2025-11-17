<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\QboToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class QboItemLookupController extends Controller
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
        $term = trim((string) $request->query('q', ''));
        $limit = (int) $request->query('limit', 25);
        $token = QboToken::latest('updated_at')->first();
        if (!$token) {
            return response()->json(['error' => 'QBO not connected'], 422);
        }
        $realmId = $token->realm_id;
        // Build a safe query: Service items only; search by Name or FullyQualifiedName
        $where = "Type = 'Service'";
        if ($term !== '') {
            // escape single quotes in term
            $safe = str_replace("'", "''", $term);
            $where .= " and (Name like '%{$safe}%' or FullyQualifiedName like '%{$safe}%')";
        }
        $query = "select Id, Name, Type, Active, FullyQualifiedName from Item where {$where} order by FullyQualifiedName";
        $res = Http::withHeaders($this->authHeaders())
            ->get($this->baseUrl($realmId).'/query', ['query' => $query, 'minorversion' => 65]);
        if (!$res->ok()) {
            return response()->json(['error' => 'QBO query failed', 'details' => $res->json() ?: $res->body()], 500);
        }
        $items = $res->json()['QueryResponse']['Item'] ?? [];
        // Normalize to simple array
        $out = collect($items)->take($limit)->map(function($i){
            return [
                'id' => $i['Id'] ?? null,
                'name' => $i['Name'] ?? null,
                'full_name' => $i['FullyQualifiedName'] ?? ($i['Name'] ?? ''),
                'active' => (bool) ($i['Active'] ?? true),
                'type' => $i['Type'] ?? null,
            ];
        })->values();
        return response()->json(['items' => $out]);
    }
}
