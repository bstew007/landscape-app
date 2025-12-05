<?php

namespace App\Mail;

use App\Models\Estimate;
use App\Support\ScopeSummaryBuilder;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EstimateTemplateMail extends Mailable
{
    use Queueable, SerializesModels;

    public Estimate $estimate;
    public string $template;
    public ?string $customSubject;
    public ?string $customMessage;

    public function __construct(
        Estimate $estimate, 
        string $template = 'full-detail',
        ?string $customSubject = null,
        ?string $customMessage = null
    ) {
        $this->estimate = $estimate->loadMissing([
            'client',
            'property',
            'items.area',
            'areas'
        ]);
        $this->template = $template;
        $this->customSubject = $customSubject;
        $this->customMessage = $customMessage;
    }

    public function build()
    {
        $scopeSummaries = ScopeSummaryBuilder::fromEstimate($this->estimate);
        
        // Group items by work area
        $itemsByArea = $this->estimate->items->groupBy('area_id');
        
        // Filter items based on template
        $filteredItemsByArea = $itemsByArea->map(function ($items) {
            if ($this->template === 'materials-only') {
                return $items->where('item_type', 'material');
            } elseif ($this->template === 'labor-only') {
                return $items->where('item_type', 'labor');
            }
            return $items;
        });

        $viewData = [
            'estimate' => $this->estimate,
            'scopeSummaries' => $scopeSummaries,
            'template' => $this->template,
            'itemsByArea' => $filteredItemsByArea,
        ];
        
        // Use template-specific view if it exists, otherwise use default
        $viewName = "estimates.print-templates.{$this->template}";
        if (!view()->exists($viewName)) {
            $viewName = 'estimates.print';
        }

        $pdf = Pdf::loadView($viewName, $viewData)->output();
        
        $templateLabel = match($this->template) {
            'full-detail' => 'Full Detail',
            'proposal' => 'Proposal',
            'materials-only' => 'Materials',
            'labor-only' => 'Labor',
            'summary' => 'Summary',
            default => 'Estimate'
        };

        $subject = $this->customSubject ?? "Estimate #{$this->estimate->id} - {$templateLabel} from " . config('app.name');
        $filename = "Estimate-{$this->estimate->id}-{$templateLabel}.pdf";

        return $this->subject($subject)
            ->view('emails.estimates.template-sent', [
                'estimate' => $this->estimate,
                'template' => $this->template,
                'customMessage' => $this->customMessage,
            ])
            ->attachData(
                $pdf,
                $filename,
                ['mime' => 'application/pdf']
            );
    }
}
