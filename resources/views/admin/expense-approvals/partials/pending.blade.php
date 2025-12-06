@if($expenses->isEmpty())
  <div class="text-center py-12">
    <svg class="h-16 w-16 text-brand-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
    </svg>
    <p class="text-brand-600 font-medium">No expenses pending approval</p>
    <p class="text-sm text-brand-500 mt-1">All caught up!</p>
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
        <button type="button" onclick="submitBulkAction('{{ route('admin.expense-approvals.bulk-approve') }}')" 
                class="bulk-action-button px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-semibold rounded-lg transition-all">
          Approve Selected
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
                  </div>
                  
                  <div class="grid grid-cols-2 md:grid-cols-4 gap-3 text-sm">
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
                      <p class="text-brand-600 text-xs">Submitted By</p>
                      <p class="font-semibold text-brand-900">{{ $expense->submittedBy->name ?? '—' }}</p>
                    </div>
                  </div>

                  @if($expense->description)
                    <p class="text-sm text-brand-700 mt-2">{{ $expense->description }}</p>
                  @endif

                  @if($expense->attachments->count() > 0)
                    <div class="flex items-center gap-2 mt-2">
                      <svg class="h-4 w-4 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                      </svg>
                      <span class="text-xs font-semibold text-brand-700">{{ $expense->attachments->count() }} attachment(s)</span>
                    </div>
                  @endif
                </div>

                <div class="flex flex-col gap-2">
                  <form action="{{ route('admin.expense-approvals.approve', $expense) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-semibold rounded-lg transition-all whitespace-nowrap">
                      Approve
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
