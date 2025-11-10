@extends('layouts.sidebar')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div>
        <p class="text-sm uppercase tracking-wide text-gray-500">Email Preview</p>
        <h1 class="text-3xl font-bold text-gray-900">Estimate Email</h1>
        <p class="text-gray-600">Preview the email before sending it to the client.</p>
    </div>

    @if (session('success'))
        <div class="p-4 rounded bg-green-100 text-green-800">
            {{ session('success') }}
        </div>
    @endif

    @if ($estimate->email_send_count)
        <div class="p-4 rounded border border-blue-200 bg-blue-50 text-sm text-blue-900">
            This estimate was last emailed on
            <strong>{{ $estimate->email_last_sent_at?->timezone(config('app.timezone'))->format('M j, Y g:i A') ?? 'unknown' }}</strong>
            ({{ $estimate->email_send_count }} {{ \Illuminate\Support\Str::plural('send', $estimate->email_send_count) }}).
        </div>
    @endif

    <section class="bg-white rounded shadow p-4">
        <div class="prose max-w-none">
            {!! $html !!}
        </div>
    </section>

    <div class="flex flex-wrap gap-2">
        <form action="{{ route('estimates.email', $estimate) }}" method="POST">
            @csrf
            <button
                type="submit"
                class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700"
                data-email-confirm="{{ $estimate->email_send_count ? 'true' : 'false' }}"
            >
                {{ $estimate->email_send_count ? 'Resend Email' : 'Send Email' }}
            </button>
        </form>
        <a href="{{ route('estimates.index') }}" class="inline-flex items-center px-4 py-2 border rounded text-gray-700 hover:bg-gray-50">Done</a>
    </div>
</div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const sendButton = document.querySelector('button[data-email-confirm]');
            if (!sendButton) {
                return;
            }

            sendButton.addEventListener('click', (event) => {
                if (sendButton.dataset.emailConfirm === 'true') {
                    const proceed = confirm('This estimate has already been emailed. Send another copy?');
                    if (!proceed) {
                        event.preventDefault();
                    }
                }
            });
        });
    </script>
@endpush
