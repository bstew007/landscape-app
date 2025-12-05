<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetExpense;
use App\Models\AssetExpenseAttachment;
use App\Models\AssetIssue;
use App\Services\QboExpenseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AssetExpenseController extends Controller
{
    public function create(Asset $asset)
    {
        $issues = $asset->issues()->whereIn('status', ['Reported', 'In Progress'])->get();
        
        return view('assets.expenses.create', compact('asset', 'issues'));
    }

    public function store(Request $request, Asset $asset)
    {
        $validated = $request->validate([
            'category' => 'required|in:fuel,repairs,general',
            'subcategory' => 'nullable|string|max:255',
            'asset_issue_id' => 'nullable|exists:asset_issues,id',
            'vendor' => 'nullable|string|max:255',
            'amount' => 'required|numeric|min:0',
            'expense_date' => 'required|date',
            'odometer_hours' => 'nullable|integer|min:0',
            'description' => 'nullable|string',
            'notes' => 'nullable|string',
            'receipt_number' => 'nullable|string|max:255',
            'is_reimbursable' => 'boolean',
            'attachments.*' => 'nullable|file|mimes:pdf,jpg,jpeg,png,gif|max:10240', // 10MB max
        ]);

        // Validate that repairs must have an asset_issue_id
        if ($validated['category'] === 'repairs' && empty($validated['asset_issue_id'])) {
            return back()->withErrors(['asset_issue_id' => 'Repair expenses must be linked to an issue.'])->withInput();
        }

        $validated['asset_id'] = $asset->id;
        $validated['submitted_by'] = auth()->id();
        $validated['is_reimbursable'] = $request->has('is_reimbursable');

        $expense = AssetExpense::create($validated);

        // Handle file uploads
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('asset-expenses', 'public');
                
                AssetExpenseAttachment::create([
                    'asset_expense_id' => $expense->id,
                    'file_path' => $path,
                    'file_name' => $file->getClientOriginalName(),
                    'file_type' => $file->getClientMimeType(),
                    'file_size' => $file->getSize(),
                    'uploaded_by' => auth()->id(),
                ]);
            }
        }

        return redirect()
            ->route('assets.show', $asset)
            ->with('success', 'Expense added successfully!');
    }

    public function edit(Asset $asset, AssetExpense $expense)
    {
        // Ensure the expense belongs to this asset
        if ($expense->asset_id !== $asset->id) {
            abort(403);
        }

        $issues = $asset->issues()->whereIn('status', ['Reported', 'In Progress'])->get();
        
        return view('assets.expenses.edit', compact('asset', 'expense', 'issues'));
    }

    public function update(Request $request, Asset $asset, AssetExpense $expense)
    {
        // Ensure the expense belongs to this asset
        if ($expense->asset_id !== $asset->id) {
            abort(403);
        }

        $validated = $request->validate([
            'category' => 'required|in:fuel,repairs,general',
            'subcategory' => 'nullable|string|max:255',
            'asset_issue_id' => 'nullable|exists:asset_issues,id',
            'vendor' => 'nullable|string|max:255',
            'amount' => 'required|numeric|min:0',
            'expense_date' => 'required|date',
            'odometer_hours' => 'nullable|integer|min:0',
            'description' => 'nullable|string',
            'notes' => 'nullable|string',
            'receipt_number' => 'nullable|string|max:255',
            'is_reimbursable' => 'boolean',
            'attachments.*' => 'nullable|file|mimes:pdf,jpg,jpeg,png,gif|max:10240',
        ]);

        // Validate that repairs must have an asset_issue_id
        if ($validated['category'] === 'repairs' && empty($validated['asset_issue_id'])) {
            return back()->withErrors(['asset_issue_id' => 'Repair expenses must be linked to an issue.'])->withInput();
        }

        $validated['is_reimbursable'] = $request->has('is_reimbursable');

        $expense->update($validated);

        // Handle new file uploads
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('asset-expenses', 'public');
                
                AssetExpenseAttachment::create([
                    'asset_expense_id' => $expense->id,
                    'file_path' => $path,
                    'file_name' => $file->getClientOriginalName(),
                    'file_type' => $file->getClientMimeType(),
                    'file_size' => $file->getSize(),
                    'uploaded_by' => auth()->id(),
                ]);
            }
        }

        return redirect()
            ->route('assets.show', $asset)
            ->with('success', 'Expense updated successfully!');
    }

    public function destroy(Asset $asset, AssetExpense $expense)
    {
        // Ensure the expense belongs to this asset
        if ($expense->asset_id !== $asset->id) {
            abort(403);
        }

        // Delete all attachments (will trigger file deletion via model boot)
        $expense->attachments()->delete();
        
        $expense->delete();

        return redirect()
            ->route('assets.show', $asset)
            ->with('success', 'Expense approved successfully!');
    }

    public function syncToQbo(Asset $asset, AssetExpense $expense, QboExpenseService $qboService)
    {
        // Ensure the expense belongs to this asset
        if ($expense->asset_id !== $asset->id) {
            abort(403);
        }

        // Sync to QBO
        $result = $qboService->syncExpense($expense);

        if ($result['success']) {
            return redirect()
                ->route('assets.show', $asset)
                ->with('success', $result['message']);
        }

        return redirect()
            ->route('assets.show', $asset)
            ->with('error', $result['message']);
    }

    public function deleteAttachment(Asset $asset, AssetExpense $expense, AssetExpenseAttachment $attachment)
    {
        // Ensure the attachment belongs to this expense and asset
        if ($attachment->asset_expense_id !== $expense->id || $expense->asset_id !== $asset->id) {
            abort(403);
        }

        $attachment->delete();

        return back()->with('success', 'Attachment deleted successfully!');
    }

    public function downloadAttachment(Asset $asset, AssetExpense $expense, AssetExpenseAttachment $attachment)
    {
        // Ensure the attachment belongs to this expense and asset
        if ($attachment->asset_expense_id !== $expense->id || $expense->asset_id !== $asset->id) {
            abort(403);
        }

        return Storage::disk('public')->download($attachment->file_path, $attachment->file_name);
    }

    public function approve(Asset $asset, AssetExpense $expense)
    {
        // Ensure the expense belongs to this asset
        if ($expense->asset_id !== $asset->id) {
            abort(403);
        }

        $expense->update([
            'approved_by' => auth()->id(),
        ]);

        return back()->with('success', 'Expense approved successfully!');
    }
}
