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

    public function __construct(public int $contactId) {
        // Respect DB transactions; process only after commit
        $this->afterCommit = true;
    }

    public function handle(QboCustomerService $svc): void
    {
        try {
            $contact = Contact::find($this->contactId);
            if (!$contact) return;
            // Debounce: if updated very recently, delay and re-dispatch
            if ($contact->wasRecentlyCreated || now()->diffInSeconds($contact->updated_at) < 2) {
                static::dispatch($this->contactId)->delay(now()->addSeconds(2))->onQueue('qbo');
                return;
            }
            $svc->upsert($contact);
        } catch (\Throwable $e) {
            // Never fail the HTTP request when QUEUE_CONNECTION=sync
            \Log::error('QBO sync job failed', [
                'contact_id' => $this->contactId,
                'error' => $e->getMessage(),
            ]);
            // Optionally we could mark a sync error field; for now, just log.
        }
    }
}
