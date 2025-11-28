<?php

namespace App\Http\Controllers;

use App\Models\Estimate;
use App\Models\EstimatePurchaseOrder;
use App\Services\PurchaseOrderService;
use Illuminate\Http\Request;

class PurchaseOrderController extends Controller
{
    public function __construct(protected PurchaseOrderService $service)
    {
    }

    /**
     * Display a listing of purchase orders.
     */
    public function index(Request $request)
    {
        $query = EstimatePurchaseOrder::with(['estimate', 'supplier'])
            ->orderBy('created_at', 'desc');

        if ($request->has('estimate_id')) {
            $query->where('estimate_id', $request->estimate_id);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $purchaseOrders = $query->paginate(50);

        return view('purchase-orders.index', compact('purchaseOrders'));
    }

    /**
     * Display the specified purchase order.
     */
    public function show(EstimatePurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load(['estimate', 'supplier', 'items.material', 'items.estimateItem']);

        return view('purchase-orders.show', compact('purchaseOrder'));
    }

    /**
     * Generate purchase orders from an estimate.
     */
    public function generateFromEstimate(Request $request, Estimate $estimate)
    {
        $replaceExisting = $request->boolean('replace_existing', false);

        $purchaseOrders = $this->service->generatePOsFromEstimate($estimate, $replaceExisting);

        if ($purchaseOrders->isEmpty()) {
            return back()->with('error', 'No materials found to create purchase orders.');
        }

        $count = $purchaseOrders->count();
        $message = $count === 1 
            ? "Generated 1 purchase order." 
            : "Generated {$count} purchase orders.";

        return back()->with('success', $message);
    }

    /**
     * Update the status of a purchase order.
     */
    public function updateStatus(Request $request, EstimatePurchaseOrder $purchaseOrder)
    {
        $request->validate([
            'status' => ['required', 'in:draft,sent,received,cancelled'],
        ]);

        $this->service->updateStatus($purchaseOrder, $request->status);

        return back()->with('success', 'Purchase order status updated.');
    }

    /**
     * Remove the specified purchase order from storage.
     */
    public function destroy(EstimatePurchaseOrder $purchaseOrder)
    {
        $estimateId = $purchaseOrder->estimate_id;
        $this->service->deletePurchaseOrder($purchaseOrder);

        if (request()->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->route('estimates.show', $estimateId)
            ->with('success', 'Purchase order deleted.');
    }

    /**
     * Print the purchase order.
     */
    public function print(EstimatePurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load(['estimate', 'supplier', 'items.material', 'items.estimateItem']);

        $download = request()->boolean('download');

        if ($download) {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('purchase-orders.print', compact('purchaseOrder'));
            return $pdf->download("PO-{$purchaseOrder->po_number}.pdf");
        }

        return view('purchase-orders.print', compact('purchaseOrder'));
    }

    /**
     * Print multiple purchase orders at once.
     */
    public function printBatch(Request $request)
    {
        $request->validate([
            'po_ids' => ['required', 'array'],
            'po_ids.*' => ['integer', 'exists:estimate_purchase_orders,id'],
        ]);

        $purchaseOrders = EstimatePurchaseOrder::with(['estimate', 'supplier', 'items.material', 'items.estimateItem'])
            ->whereIn('id', $request->po_ids)
            ->get();

        $download = $request->boolean('download');

        if ($download) {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('purchase-orders.print-batch', compact('purchaseOrders'));
            return $pdf->download("purchase-orders-" . date('Y-m-d') . ".pdf");
        }

        return view('purchase-orders.print-batch', compact('purchaseOrders'));
    }
}
