<section class="bg-white rounded-lg shadow p-6 space-y-4">
    <h2 class="text-lg font-semibold text-gray-900">Contact Information</h2>
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 text-sm">
        <div>
            <p class="text-xs uppercase text-gray-500">First Name</p>
            <p class="font-semibold text-gray-900">{{ $contact->first_name ?? '—' }}</p>
        </div>
        <div>
            <p class="text-xs uppercase text-gray-500">Last Name</p>
            <p class="font-semibold text-gray-900">{{ $contact->last_name ?? '—' }}</p>
        </div>
        <div>
            <p class="text-xs uppercase text-gray-500">Company</p>
            <p class="font-semibold text-gray-900">{{ $contact->company_name ?? '—' }}</p>
        </div>
        <div>
            <p class="text-xs uppercase text-gray-500">Email</p>
            <p class="font-semibold text-gray-900">{{ $contact->email ?? '—' }}</p>
        </div>
        <div>
            <p class="text-xs uppercase text-gray-500">Alt Email</p>
            <p class="font-semibold text-gray-900">{{ $contact->email2 ?? '—' }}</p>
        </div>
        <div>
            <p class="text-xs uppercase text-gray-500">Phone</p>
            <p class="font-semibold text-gray-900">{{ $contact->phone ?? '—' }}</p>
        </div>
        <div>
            <p class="text-xs uppercase text-gray-500">Alt Phone</p>
            <p class="font-semibold text-gray-900">{{ $contact->phone2 ?? '—' }}</p>
        </div>
        <div class="sm:col-span-2 lg:col-span-3">
            <p class="text-xs uppercase text-gray-500">Billing Address</p>
            <p class="font-semibold text-gray-900">{{ $contact->address ?? '—' }}</p>
        </div>
    </div>
</section>
