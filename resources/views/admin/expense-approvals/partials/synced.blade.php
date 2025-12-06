@if($expenses->isEmpty())
  <div class="text-center py-12">
    <svg class="h-16 w-16 text-brand-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
    </svg>
    <p class="text-brand-600 font-medium">No recent syncs</p>
    <p class="text-sm text-brand-500 mt-1">Synced expenses from the last 30 days will appear here</p>
  </div>
@else
  <div class="space-y-3">
    @foreach($expenses as $expense)
      <div class="border-2 border-green-100 bg-green-50/30 rounded-xl p-4">
        <div class="flex items-start justify-between gap-4">
          <div class="flex-1">
            <div class="flex items-center gap-2 mb-2">
              <h3 class="font-bold text-brand-900">{{ $expense->asset->name }}</h3>
              <span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                {{ ucfirst($expense->category) }}
              </span>
              <span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                ✓ Synced to QBO
              </span>
            </div>
            
            <div class="grid grid-cols-2 md:grid-cols-5 gap-3 text-sm">
              <div>
                <p class="text-brand-600 text-xs">Amount</p>
                <p class="font-bold text-brand-900">${{ number_format($expense->amount, 2) }}</p>
              </div>
              <div>
                <p class="text-brand-600 text-xs">Expense Date</p>
                <p class="font-semibold text-brand-900">{{ $expense->expense_date->format('M d, Y') }}</p>
              </div>
              <div>
                <p class="text-brand-600 text-xs">Synced</p>
                <p class="font-semibold text-brand-900">{{ $expense->qbo_synced_at->format('M d, Y') }}</p>
              </div>
              <div>
                <p class="text-brand-600 text-xs">QBO ID</p>
                <p class="font-semibold text-brand-900">{{ $expense->qbo_expense_id }}</p>
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

          <a href="{{ route('assets.show', $expense->asset) }}#expense-{{ $expense->id }}" 
             class="px-4 py-2 bg-white border-2 border-brand-200 hover:border-brand-300 text-brand-700 text-sm font-semibold rounded-lg transition-all whitespace-nowrap">
            View Details
          </a>
        </div>
      </div>
    @endforeach
  </div>
@endif
