@extends('layouts.sidebar')

@section('content')
    <div class="max-w-4xl mx-auto space-y-6">
        {{-- Branded Header --}}
        <section class="rounded-[20px] sm:rounded-[28px] bg-gradient-to-br from-brand-900 via-brand-800 to-brand-700 text-white p-6 sm:p-8 shadow-2xl border border-brand-800/40">
            <div class="flex items-center gap-4">
                <div class="h-14 w-14 rounded-2xl bg-white/10 backdrop-blur-sm border border-white/20 flex items-center justify-center flex-shrink-0">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-7 w-7 text-white">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-[0.3em] text-brand-200/80">Edit Expense</p>
                    <h1 class="text-2xl sm:text-3xl font-semibold text-white mt-1">{{ $asset->name }}</h1>
                    <p class="text-sm text-brand-100/85 mt-1">Update expense details for this asset.</p>
                </div>
            </div>
        </section>

        @if ($errors->any())
            <div class="rounded-xl bg-red-50 border-2 border-red-200 text-red-800 px-5 py-4">
                <div class="flex items-start gap-3">
                    <svg class="h-5 w-5 text-red-600 flex-shrink-0 mt-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg>
                    <ul class="list-disc list-inside space-y-1">
                        @foreach ($errors->all() as $error)
                            <li class="text-sm">{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        <form action="{{ route('assets.expenses.update', [$asset, $expense]) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="rounded-2xl bg-white border-2 border-brand-100 shadow-sm p-6 space-y-6">
                <h2 class="text-lg font-bold text-brand-900 border-b-2 border-brand-100 pb-3">Expense Details</h2>

                {{-- Category --}}
                <div>
                    <label class="block text-sm font-semibold text-brand-800 mb-2">Category *</label>
                    <select name="category" id="category" required 
                        class="w-full px-4 py-2.5 border-2 border-brand-200 rounded-xl focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 transition-all"
                        onchange="toggleIssueField()">
                        <option value="">Select category...</option>
                        <option value="fuel" {{ old('category', $expense->category) == 'fuel' ? 'selected' : '' }}>Fuel</option>
                        <option value="repairs" {{ old('category', $expense->category) == 'repairs' ? 'selected' : '' }}>Repairs</option>
                        <option value="general" {{ old('category', $expense->category) == 'general' ? 'selected' : '' }}>General (Insurance, Registration)</option>
                    </select>
                </div>

                {{-- Subcategory --}}
                <div>
                    <label class="block text-sm font-semibold text-brand-800 mb-2">Subcategory</label>
                    <input type="text" name="subcategory" value="{{ old('subcategory', $expense->subcategory) }}" 
                        class="w-full px-4 py-2.5 border-2 border-brand-200 rounded-xl focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 transition-all"
                        placeholder="e.g., Gas, Diesel, Insurance, Registration">
                </div>

                {{-- Linked Issue (for repairs) --}}
                <div id="issue-field" style="display: {{ old('category', $expense->category) == 'repairs' ? 'block' : 'none' }}">
                    <label class="block text-sm font-semibold text-brand-800 mb-2">
                        Linked Issue <span class="text-red-600">*</span>
                    </label>
                    <select name="asset_issue_id" 
                        class="w-full px-4 py-2.5 border-2 border-brand-200 rounded-xl focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 transition-all">
                        <option value="">Select issue...</option>
                        @foreach($issues as $issue)
                            <option value="{{ $issue->id }}" {{ old('asset_issue_id', $expense->asset_issue_id) == $issue->id ? 'selected' : '' }}>
                                {{ $issue->title }} ({{ $issue->status }})
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Amount --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-brand-800 mb-2">Amount *</label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-brand-600 font-semibold">$</span>
                            <input type="number" name="amount" value="{{ old('amount', $expense->amount) }}" step="0.01" min="0" required
                                class="w-full pl-8 pr-4 py-2.5 border-2 border-brand-200 rounded-xl focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 transition-all">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-brand-800 mb-2">Expense Date *</label>
                        <input type="date" name="expense_date" value="{{ old('expense_date', $expense->expense_date->format('Y-m-d')) }}" required
                            class="w-full px-4 py-2.5 border-2 border-brand-200 rounded-xl focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 transition-all">
                    </div>
                </div>

                {{-- Vendor & Receipt --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-brand-800 mb-2">Vendor</label>
                        <input type="text" name="vendor" value="{{ old('vendor', $expense->vendor) }}"
                            class="w-full px-4 py-2.5 border-2 border-brand-200 rounded-xl focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 transition-all">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-brand-800 mb-2">Receipt Number</label>
                        <input type="text" name="receipt_number" value="{{ old('receipt_number', $expense->receipt_number) }}"
                            class="w-full px-4 py-2.5 border-2 border-brand-200 rounded-xl focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 transition-all">
                    </div>
                </div>

                {{-- Odometer/Hours --}}
                <div>
                    <label class="block text-sm font-semibold text-brand-800 mb-2">Odometer / Hours Reading</label>
                    <input type="number" name="odometer_hours" value="{{ old('odometer_hours', $expense->odometer_hours) }}" min="0"
                        class="w-full px-4 py-2.5 border-2 border-brand-200 rounded-xl focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 transition-all">
                </div>

                {{-- Description --}}
                <div>
                    <label class="block text-sm font-semibold text-brand-800 mb-2">Description</label>
                    <textarea name="description" rows="3"
                        class="w-full px-4 py-2.5 border-2 border-brand-200 rounded-xl focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 transition-all">{{ old('description', $expense->description) }}</textarea>
                </div>

                {{-- Notes --}}
                <div>
                    <label class="block text-sm font-semibold text-brand-800 mb-2">Notes</label>
                    <textarea name="notes" rows="2"
                        class="w-full px-4 py-2.5 border-2 border-brand-200 rounded-xl focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 transition-all">{{ old('notes', $expense->notes) }}</textarea>
                </div>

                {{-- Reimbursable --}}
                <div>
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" name="is_reimbursable" value="1" {{ old('is_reimbursable', $expense->is_reimbursable) ? 'checked' : '' }}
                            class="w-5 h-5 rounded border-2 border-brand-300 text-brand-600 focus:ring-2 focus:ring-brand-500/20">
                        <span class="text-sm font-semibold text-brand-800">This is a reimbursable expense</span>
                    </label>
                </div>

                {{-- Existing Attachments --}}
                @if($expense->attachments->count() > 0)
                    <div>
                        <label class="block text-sm font-semibold text-brand-800 mb-2">Existing Attachments</label>
                        <div class="space-y-2">
                            @foreach($expense->attachments as $attachment)
                                <div class="flex items-center justify-between p-3 bg-brand-50 rounded-lg">
                                    <div class="flex items-center gap-3">
                                        <svg class="h-5 w-5 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                        </svg>
                                        <div>
                                            <p class="text-sm font-medium text-brand-900">{{ $attachment->file_name }}</p>
                                            <p class="text-xs text-brand-600">{{ $attachment->file_size_human }}</p>
                                        </div>
                                    </div>
                                    <div class="flex gap-2">
                                        <a href="{{ route('assets.expenses.attachments.download', [$asset, $expense, $attachment]) }}" 
                                            class="px-3 py-1 bg-brand-600 hover:bg-brand-700 text-white text-xs font-medium rounded-lg transition-all">
                                            Download
                                        </a>
                                        <form action="{{ route('assets.expenses.attachments.delete', [$asset, $expense, $attachment]) }}" method="POST" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" onclick="return confirm('Delete this attachment?')"
                                                class="px-3 py-1 bg-red-600 hover:bg-red-700 text-white text-xs font-medium rounded-lg transition-all">
                                                Delete
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Add New Attachments --}}
                <div>
                    <label class="block text-sm font-semibold text-brand-800 mb-2">Add More Attachments</label>
                    <input type="file" name="attachments[]" multiple accept=".pdf,.jpg,.jpeg,.png,.gif"
                        class="w-full px-4 py-2.5 border-2 border-brand-200 rounded-xl focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 transition-all">
                    <p class="text-xs text-brand-600 mt-1">Upload additional receipts or documents (PDF, JPG, PNG - Max 10MB each)</p>
                </div>
            </div>

            {{-- Actions --}}
            <div class="flex gap-3">
                <button type="submit" 
                    class="flex-1 px-6 py-3 bg-brand-600 hover:bg-brand-700 text-white font-semibold rounded-xl transition-all shadow-sm">
                    Update Expense
                </button>
                <a href="{{ route('assets.show', $asset) }}" 
                    class="px-6 py-3 bg-brand-100 hover:bg-brand-200 text-brand-900 font-semibold rounded-xl transition-all">
                    Cancel
                </a>
            </div>
        </form>
    </div>

    <script>
        function toggleIssueField() {
            const category = document.getElementById('category').value;
            const issueField = document.getElementById('issue-field');
            const issueSelect = document.querySelector('[name="asset_issue_id"]');
            
            if (category === 'repairs') {
                issueField.style.display = 'block';
                issueSelect.required = true;
            } else {
                issueField.style.display = 'none';
                issueSelect.required = false;
                issueSelect.value = '';
            }
        }
    </script>
@endsection
