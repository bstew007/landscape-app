<?php

namespace App\Mail;

use App\Models\Calculation;
use App\Models\SiteVisit;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SynTurfEstimateMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public SiteVisit $siteVisit,
        public Calculation $calculation,
        public string $subjectLine,
        public string $bodyMessage,
        public string $pdfBase64
    ) {
    }

    public function build(): self
    {
        return $this->subject($this->subjectLine)
            ->view('emails.syn-turf-estimate')
            ->with([
                'siteVisit' => $this->siteVisit,
                'calculation' => $this->calculation,
                'messageBody' => $this->bodyMessage,
            ]);

        $pdfData = base64_decode($this->pdfBase64);

        if ($pdfData !== false) {
            $this->attachData(
                $pdfData,
                'synthetic_turf_estimate.pdf',
                ['mime' => 'application/pdf']
            );
        }

        return $this;
    }
}
