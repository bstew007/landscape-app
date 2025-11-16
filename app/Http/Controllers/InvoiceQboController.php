<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Services\QboInvoiceService;
use Illuminate\Http\Request;

class InvoiceQboController extends Controller
{
    public function create(Invoice $invoice, QboInvoiceService $svc)
    {
        try {
            $svc->create($invoice);
            return back()->with('success', 'Invoice created in QuickBooks');
        } catch (\Throwable $e) {
            return back()->with('error', 'QBO invoice create failed: '.$e->getMessage());
        }
    }

    public function refresh(Invoice $invoice, QboInvoiceService $svc)
    {
        try {
            $svc->refresh($invoice);
            return back()->with('success', 'Invoice refreshed from QuickBooks');
        } catch (\Throwable $e) {
            return back()->with('error', 'QBO invoice refresh failed: '.$e->getMessage());
        }
    }
}
