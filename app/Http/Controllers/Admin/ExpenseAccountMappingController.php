<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ExpenseAccountMapping;
use App\Models\QboToken;
use App\Services\QboExpenseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ExpenseAccountMappingController extends Controller
{
    protected $qboService;

    public function __construct(QboExpenseService $qboService)
    {
        $this->qboService = $qboService;
    }

    public function index()
    {
        $mappings = ExpenseAccountMapping::orderBy('category')->get();
        
        // If no mappings exist, seed them
        if ($mappings->isEmpty()) {
            $this->seedDefaultMappings();
            $mappings = ExpenseAccountMapping::orderBy('category')->get();
        }

        return view('admin.expense-accounts.index', compact('mappings'));
    }

    public function create()
    {
        // Fetch QBO Chart of Accounts
        $qboAccounts = $this->fetchQboAccounts();
        
        return view('admin.expense-accounts.create', [
            'qboAccounts' => $qboAccounts,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category' => 'required|string|max:100|unique:expense_account_mappings,category',
            'category_label' => 'required|string|max:255',
            'qbo_account_id' => 'nullable|string|max:100',
            'qbo_account_name' => 'nullable|string|max:255',
            'qbo_account_type' => 'nullable|string|max:100',
            'is_active' => 'sometimes|boolean',
        ]);

        // Default is_active to true if not provided
        $validated['is_active'] = $validated['is_active'] ?? true;

        ExpenseAccountMapping::create($validated);

        return redirect()
            ->route('admin.expense-accounts.index')
            ->with('success', 'Expense category created successfully!');
    }

    public function edit(ExpenseAccountMapping $expenseAccount)
    {
        // Fetch QBO Chart of Accounts
        $qboAccounts = $this->fetchQboAccounts();
        
        return view('admin.expense-accounts.edit', [
            'mapping' => $expenseAccount,
            'qboAccounts' => $qboAccounts,
        ]);
    }

    public function update(Request $request, ExpenseAccountMapping $expenseAccount)
    {
        $validated = $request->validate([
            'category_label' => 'sometimes|string|max:255',
            'qbo_account_id' => 'nullable|string|max:100',
            'qbo_account_name' => 'nullable|string|max:255',
            'qbo_account_type' => 'nullable|string|max:100',
            'is_active' => 'sometimes|boolean',
        ]);

        $expenseAccount->update($validated);

        return redirect()
            ->route('admin.expense-accounts.index')
            ->with('success', 'Expense account mapping updated successfully!');
    }

    public function destroy(ExpenseAccountMapping $expenseAccount)
    {
        $expenseAccount->delete();

        return redirect()
            ->route('admin.expense-accounts.index')
            ->with('success', 'Expense category deleted successfully!');
    }

    public function syncAll()
    {
        $qboAccounts = $this->fetchQboAccounts();
        
        if (empty($qboAccounts)) {
            return redirect()
                ->route('admin.expense-accounts.index')
                ->with('error', 'Could not fetch QBO Chart of Accounts. Please check your QBO connection.');
        }

        return redirect()
            ->route('admin.expense-accounts.index')
            ->with('success', 'QBO accounts fetched successfully! Edit each mapping to assign accounts.');
    }

    /**
     * Seed default expense account mappings.
     */
    protected function seedDefaultMappings()
    {
        $defaults = ExpenseAccountMapping::getDefaultCategories();
        
        foreach ($defaults as $category => $label) {
            ExpenseAccountMapping::create([
                'category' => $category,
                'category_label' => $label,
            ]);
        }
    }

    /**
     * Fetch Chart of Accounts from QBO.
     */
    protected function fetchQboAccounts(): array
    {
        $token = QboToken::latest('updated_at')->first();
        
        if (!$token) {
            return [];
        }

        try {
            // Refresh token if needed
            if ($token->expires_at && now()->diffInSeconds($token->expires_at, false) < 60) {
                $this->refreshToken($token);
            }

            $env = config('qbo.environment');
            $host = $env === 'production' ? 'quickbooks.api.intuit.com' : 'sandbox-quickbooks.api.intuit.com';
            $baseUrl = "https://{$host}/v3/company/{$token->realm_id}";

            // Query for Expense accounts
            $query = "SELECT * FROM Account WHERE AccountType = 'Expense' AND Active = true ORDER BY Name";
            $url = $baseUrl . "/query?query=" . urlencode($query);

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token->access_token,
                'Accept' => 'application/json',
            ])->get($url);

            if ($response->status() === 401) {
                $this->refreshToken($token);
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $token->access_token,
                    'Accept' => 'application/json',
                ])->get($url);
            }

            if ($response->successful()) {
                $data = $response->json();
                return $data['QueryResponse']['Account'] ?? [];
            }

            Log::warning('Failed to fetch QBO Chart of Accounts', [
                'status' => $response->status(),
                'response' => $response->body(),
            ]);

            return [];

        } catch (\Exception $e) {
            Log::error('Error fetching QBO Chart of Accounts', [
                'error' => $e->getMessage(),
            ]);
            return [];
        }
    }

    /**
     * Refresh QBO token.
     */
    protected function refreshToken(QboToken $token)
    {
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
}
