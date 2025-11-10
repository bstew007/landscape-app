<?php

namespace App\Mail;

use App\Models\Estimate;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EstimateMail extends Mailable
{
    use Queueable, SerializesModels;

    public Estimate $estimate;

    public function __construct(Estimate $estimate)
    {
        $this->estimate = $estimate;
    }

    public function build()
    {
        return $this->subject("Your estimate from CFL Landscape")
            ->markdown('emails.estimates.sent', ['estimate' => $this->estimate]);
    }
}
