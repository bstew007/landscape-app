<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Services\QboCustomerService;
use Illuminate\Http\Request;

class ContactQboSyncController extends Controller
{
    public function sync(Contact $client, QboCustomerService $svc)
    {
        try {
            $svc->upsert($client);
            return back()->with('success', 'Synced to QuickBooks');
        } catch (\Throwable $e) {
            return back()->with('error', 'QBO sync failed: '.$e->getMessage());
        }
    }
}
