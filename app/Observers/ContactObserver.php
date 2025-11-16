<?php

namespace App\Observers;

use App\Models\Contact;
use App\Jobs\QboSyncContact;

class ContactObserver
{
    public function saved(Contact $contact): void
    {
        try {
            if (!config('qbo.auto_sync')) return;
            // Only queue sync if relevant fields changed
            $dirty = array_keys($contact->getChanges());
            $watched = ['first_name','last_name','company_name','email','phone','mobile','address','city','state','postal_code'];
            if (!array_intersect($dirty, $watched)) return;

            // Run after response so it cannot affect the request lifecycle (even with sync driver)
            QboSyncContact::dispatchAfterResponse($contact->id)->onQueue('qbo');
        } catch (\Throwable $e) {
            // Never interrupt saves if dispatch fails for any reason
            \Log::error('Failed to dispatch QBO sync job', [
                'contact_id' => $contact->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
