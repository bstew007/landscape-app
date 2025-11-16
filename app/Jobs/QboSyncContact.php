<?php

namespace App\Jobs;

use App\Models\Contact;
use App\Services\QboCustomerService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class QboSyncContact implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $contactId) {}

    public function handle(QboCustomerService $svc): void
    {
        $contact = Contact::find($this->contactId);
        if (!$contact) return;
        // Debounce: if updated very recently, delay and re-dispatch
        if ($contact->wasRecentlyCreated || now()->diffInSeconds($contact->updated_at) < 2) {
            static::dispatch($this->contactId)->delay(now()->addSeconds(2))->onQueue('qbo');
            return;
        }
        $svc->upsert($contact);
    }
}
