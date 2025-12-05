@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto py-8 px-4">
    <div class="mb-6">
        <a href="{{ route('estimates.show', $purchaseOrder->estimate_id) }}?tab=print" class="inline-flex items-center text-sm text-gray-600 hover:text-gray-900">
            <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Back to Print Documents
        </a>
    </div>

    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        <div class="bg-brand-600 px-6 py-4">
            <h1 class="text-xl font-semibold text-white">Email Purchase Order</h1>
            <p class="text-sm text-brand-100 mt-1">Send PO {{ $purchaseOrder->po_number }} to supplier</p>
        </div>

        <form method="POST" action="{{ route('purchase-orders.send-email', $purchaseOrder) }}" class="p-6 space-y-6">
            @csrf

            <!-- Recipient Email -->
            <div>
                <label for="recipient_email" class="block text-sm font-medium text-gray-700 mb-1">
                    Recipient Email <span class="text-red-500">*</span>
                </label>
                <input type="email" 
                       id="recipient_email" 
                       name="recipient_email" 
                       value="{{ old('recipient_email', $recipientEmail) }}"
                       required
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-transparent @error('recipient_email') border-red-500 @enderror">
                @error('recipient_email')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Subject -->
            <div>
                <label for="subject" class="block text-sm font-medium text-gray-700 mb-1">
                    Email Subject
                </label>
                <input type="text" 
                       id="subject" 
                       name="subject" 
                       value="{{ old('subject', $defaultSubject) }}"
                       maxlength="255"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-transparent @error('subject') border-red-500 @enderror">
                <p class="mt-1 text-xs text-gray-500">Leave blank to use default subject</p>
                @error('subject')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Message -->
            <div>
                <label for="message" class="block text-sm font-medium text-gray-700 mb-1">
                    Email Message
                </label>
                <textarea id="message" 
                          name="message" 
                          rows="6"
                          maxlength="2000"
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-transparent @error('message') border-red-500 @enderror">{{ old('message', $defaultMessage) }}</textarea>
                <p class="mt-1 text-xs text-gray-500">Add any special instructions for the supplier</p>
                @error('message')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- PO Info -->
            <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                <h3 class="text-sm font-semibold text-gray-900 mb-3">Purchase Order Details</h3>
                <dl class="grid grid-cols-2 gap-3 text-sm">
                    <div>
                        <dt class="text-gray-600">PO Number</dt>
                        <dd class="font-medium text-gray-900">{{ $purchaseOrder->po_number }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-600">Status</dt>
                        <dd class="font-medium text-gray-900 capitalize">{{ $purchaseOrder->status }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-600">Supplier</dt>
                        <dd class="font-medium text-gray-900">{{ $purchaseOrder->supplier->company_name ?? 'No Supplier' }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-600">Total</dt>
                        <dd class="font-medium text-gray-900">${{ number_format($purchaseOrder->total_amount ?? 0, 2) }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-600">Items</dt>
                        <dd class="font-medium text-gray-900">{{ $purchaseOrder->items->count() }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-600">Related Estimate</dt>
                        <dd class="font-medium text-gray-900">#{{ $purchaseOrder->estimate_id }}</dd>
                    </div>
                </dl>
            </div>

            <!-- Attachment Info -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <div class="flex items-start gap-3">
                    <svg class="h-5 w-5 text-blue-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                    </svg>
                    <div class="flex-1">
                        <h4 class="text-sm font-medium text-blue-900">PDF Attachment</h4>
                        <p class="text-sm text-blue-700 mt-1">
                            A PDF of Purchase Order {{ $purchaseOrder->po_number }} will be automatically attached to this email.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center justify-between gap-4 pt-4 border-t">
                <a href="{{ route('purchase-orders.print', $purchaseOrder) }}" 
                   target="_blank"
                   class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                    Preview PDF
                </a>
                
                <div class="flex items-center gap-3">
                    <a href="{{ route('estimates.show', $purchaseOrder->estimate_id) }}?tab=print" 
                       class="px-4 py-2 text-sm font-medium text-gray-700 hover:text-gray-900">
                        Cancel
                    </a>
                    <button type="submit" 
                            class="inline-flex items-center gap-2 px-6 py-2 bg-brand-600 text-white rounded-lg hover:bg-brand-700 transition font-medium">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                        Send Email
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
