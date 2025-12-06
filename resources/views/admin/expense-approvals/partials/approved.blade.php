@if($expenses->isEmpty())
  <div class="text-center py-12">
    <svg class="h-16 w-16 text-brand-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
    </svg>
    <p class="text-brand-600 font-medium">No expenses ready to sync</p>
    <p class="text-sm text-brand-500 mt-1">Approved expenses will appear here</p>
  </div>
@else
  <form id="bulk-action-form" method="POST">
    @csrf
    
    <div class="flex items-center justify-between mb-4">
      <div class="flex items-center gap-3">
        <label class="flex items-center gap-2">
          <input type="checkbox" onchange="toggleAllCheckboxes(this)" class="rounded border-2 border-brand-300 text-brand-600 focus:ring-2 focus:ring-brand-500/20">
          <span class="text-sm font-semibold text-brand-800">Select All</span>
        </label>
      </div>
      
      <div class="flex gap-2">
        <button type="button" onclick="submitBulkAction('{{ route('admin.expense-approvals.bulk-sync') }}')" 
                class="bulk-action-button px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-lg transition-all">
          Sync Selected to QBO
        </button>
      </div>
    </div>

    <div class="space-y-3">
      @foreach($expenses as $expense)
        <div class="border-2 border-brand-100 rounded-xl p-4 hover:border-brand-300 transition-all">
          <div class="flex items-start gap-4">
            <input type="checkbox" name="expense_ids[]" value="{{ $expense->id }}" 
                   onchange="updateBulkActionButtons()"
                   class="mt-1 rounded border-2 border-brand-300 text-brand-600 focus:ring-2 focus:ring-brand-500/20">
            
            <div class="flex-1 min-w-0">
              <div class="flex items-start justify-between gap-4">
                <div class="flex-1">
                  <div class="flex items-center gap-2 mb-2">
                    <h3 class="font-bold text-brand-900">{{ $expense->asset->name }}</h3>
                    <span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                      {{ ucfirst($expense->category) }}
                    </span>
                    <span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                      Approved
                    </span>
                  </div>
                  
                  <div class="grid grid-cols-2 md:grid-cols-5 gap-3 text-sm">
                    <div>
                      <p class="text-brand-600 text-xs">Amount</p>
                      <p class="font-bold text-brand-900">${{ number_format($expense->amount, 2) }}</p>
                    </div>
                    <div>
                      <p class="text-brand-600 text-xs">Date</p>
                      <p class="font-semibold text-brand-900">{{ $expense->expense_date->format('M d, Y') }}</p>
                    </div>
                    <div>
                      <p class="text-brand-600 text-xs">Vendor</p>
                      <p class="font-semibold text-brand-900">{{ $expense->vendor ?? '—' }}</p>
                    </div>
                    <div>
                      <p class="text-brand-600 text-xs">Approved By</p>
                      <p class="font-semibold text-brand-900">{{ $expense->approvedBy->name ?? '—' }}</p>
                    </div>
                    <div>
                      <p class="text-brand-600 text-xs">Attachments</p>
                      <p class="font-semibold text-brand-900">{{ $expense->attachments->count() }} file(s)</p>
                    </div>
                  </div>

                  @if($expense->description)
                    <p class="text-sm text-brand-700 mt-2">{{ $expense->description }}</p>
                  @endif
                </div>

                <div class="flex flex-col gap-2">
                  <form action="{{ route('admin.expense-approvals.sync', $expense) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-lg transition-all whitespace-nowrap">
                      Sync to QBO
                    </button>
                  </form>
                  
                  <a href="{{ route('assets.show', $expense->asset) }}#expense-{{ $expense->id }}" 
                     class="px-4 py-2 bg-white border-2 border-brand-200 hover:border-brand-300 text-brand-700 text-sm font-semibold rounded-lg transition-all text-center">
                    View Details
                  </a>
                </div>
              </div>
            </div>
          </div>
        </div>
      @endforeach
    </div>
  </form>
@endif
