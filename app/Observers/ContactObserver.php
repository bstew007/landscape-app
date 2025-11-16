<?php

namespace App\Observers;

use App\Models\Contact;
use App\Jobs\QboSyncContact;

class ContactObserver
{
    public function saved(Contact $contact): void
    {
        if (!config('qbo.auto_sync')) return;
        // Only queue sync if relevant fields changed
        $dirty = array_keys($contact->getChanges());
        $watched = ['first_name','last_name','company_name','email','phone','mobile','address','city','state','postal_code'];
        if (!array_intersect($dirty, $watched)) return;

        QboSyncContact::dispatch($contact->id)->onQueue('qbo');
    }
}
