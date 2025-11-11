<?php

namespace App\Mail;

use App\Models\Estimate;
use App\Support\ScopeSummaryBuilder;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EstimateMail extends Mailable
{
    use Queueable, SerializesModels;

    public Estimate $estimate;

    public function __construct(Estimate $estimate)
    {
        $this->estimate = $estimate->loadMissing([
            'client',
            'property',
            'siteVisit.calculations',
        ]);
    }

    public function build()
    {
        $scopeSummaries = ScopeSummaryBuilder::fromEstimate($this->estimate);

        $pdf = Pdf::loadView('estimates.print', [
            'estimate' => $this->estimate,
            'scopeSummaries' => $scopeSummaries,
        ])->output();

        return $this->subject("Your estimate from CFL Landscape")
            ->view('emails.estimates.sent', ['estimate' => $this->estimate])
            ->attachData(
                $pdf,
                "estimate-{$this->estimate->id}.pdf",
                ['mime' => 'application/pdf']
            );
    }
}
