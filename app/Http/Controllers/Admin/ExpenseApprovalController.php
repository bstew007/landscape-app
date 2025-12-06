<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AssetExpense;
use App\Services\QboExpenseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExpenseApprovalController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->get('tab', 'pending');
        
        // Get pending approval expenses
        $pendingExpenses = AssetExpense::with(['asset', 'submittedBy', 'attachments'])
            ->whereNull('approved_by')
            ->latest('expense_date')
            ->get();
        
        // Get approved but not synced expenses
        $approvedExpenses = AssetExpense::with(['asset', 'submittedBy', 'approvedBy', 'attachments'])
            ->whereNotNull('approved_by')
            ->whereNull('qbo_synced_at')
            ->latest('expense_date')
            ->get();
        
        // Get recently synced expenses (last 30 days)
        $syncedExpenses = AssetExpense::with(['asset', 'submittedBy', 'approvedBy', 'attachments'])
            ->whereNotNull('qbo_synced_at')
            ->where('qbo_synced_at', '>=', now()->subDays(30))
            ->latest('qbo_synced_at')
            ->get();
        
        return view('admin.expense-approvals.index', compact(
            'pendingExpenses',
            'approvedExpenses',
            'syncedExpenses',
            'tab'
        ));
    }

    public function approve(Request $request, AssetExpense $expense)
    {
        if ($expense->approved_by) {
            return back()->with('error', 'Expense already approved.');
        }

        $expense->update([
            'approved_by' => auth()->id(),
        ]);

        return back()->with('success', 'Expense approved successfully!');
    }

    public function bulkApprove(Request $request)
    {
        $validated = $request->validate([
            'expense_ids' => 'required|array',
            'expense_ids.*' => 'exists:asset_expenses,id',
        ]);

        $count = AssetExpense::whereIn('id', $validated['expense_ids'])
            ->whereNull('approved_by')
            ->update([
                'approved_by' => auth()->id(),
            ]);

        return back()->with('success', "{$count} expense(s) approved successfully!");
    }

    public function sync(Request $request, AssetExpense $expense, QboExpenseService $qboService)
    {
        if (!$expense->approved_by) {
            return back()->with('error', 'Expense must be approved before syncing to QBO.');
        }

        $result = $qboService->syncExpense($expense);

        if ($result['success']) {
            return back()->with('success', $result['message']);
        }

        return back()->with('error', $result['message']);
    }

    public function bulkSync(Request $request, QboExpenseService $qboService)
    {
        $validated = $request->validate([
            'expense_ids' => 'required|array',
            'expense_ids.*' => 'exists:asset_expenses,id',
        ]);

        $expenses = AssetExpense::with(['asset', 'attachments'])
            ->whereIn('id', $validated['expense_ids'])
            ->whereNotNull('approved_by')
            ->whereNull('qbo_synced_at')
            ->get();

        $successCount = 0;
        $errors = [];

        foreach ($expenses as $expense) {
            $result = $qboService->syncExpense($expense);
            
            if ($result['success']) {
                $successCount++;
            } else {
                $errors[] = "Expense #{$expense->id}: {$result['message']}";
            }
        }

        if ($successCount > 0 && empty($errors)) {
            return back()->with('success', "{$successCount} expense(s) synced to QBO successfully!");
        } elseif ($successCount > 0) {
            return back()->with('warning', "{$successCount} synced successfully. Errors: " . implode(', ', $errors));
        } else {
            return back()->with('error', 'Failed to sync expenses: ' . implode(', ', $errors));
        }
    }

    public function approveAndSync(Request $request, AssetExpense $expense, QboExpenseService $qboService)
    {
        DB::beginTransaction();
        
        try {
            // Approve if not already approved
            if (!$expense->approved_by) {
                $expense->update([
                    'approved_by' => auth()->id(),
                ]);
            }

            // Sync to QBO
            $result = $qboService->syncExpense($expense);

            if ($result['success']) {
                DB::commit();
                return back()->with('success', 'Expense approved and synced to QBO!');
            } else {
                DB::rollBack();
                return back()->with('error', $result['message']);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }
}
