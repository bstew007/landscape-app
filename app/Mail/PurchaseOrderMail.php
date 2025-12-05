<?php

namespace App\Mail;

use App\Models\EstimatePurchaseOrder;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PurchaseOrderMail extends Mailable
{
    use Queueable, SerializesModels;

    public EstimatePurchaseOrder $purchaseOrder;
    public ?string $customSubject;
    public ?string $customMessage;

    public function __construct(
        EstimatePurchaseOrder $purchaseOrder,
        ?string $customSubject = null,
        ?string $customMessage = null
    ) {
        $this->purchaseOrder = $purchaseOrder->loadMissing([
            'supplier',
            'estimate.client',
            'items'
        ]);
        $this->customSubject = $customSubject;
        $this->customMessage = $customMessage;
    }

    public function build()
    {
        $pdf = Pdf::loadView('purchase-orders.print', [
            'purchaseOrder' => $this->purchaseOrder,
        ])->output();

        $subject = $this->customSubject ?? "Purchase Order {$this->purchaseOrder->po_number} from " . config('app.name');
        $filename = "PO-{$this->purchaseOrder->po_number}.pdf";

        return $this->subject($subject)
            ->view('emails.purchase-orders.sent', [
                'purchaseOrder' => $this->purchaseOrder,
                'customMessage' => $this->customMessage,
            ])
            ->attachData(
                $pdf,
                $filename,
                ['mime' => 'application/pdf']
            );
    }
}
