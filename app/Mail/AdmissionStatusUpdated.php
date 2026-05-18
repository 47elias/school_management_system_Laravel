<?php

namespace App\Mail;

use App\Models\Admission;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AdmissionStatusUpdated extends Mailable
{
    use Queueable, SerializesModels;

    public $admission;

    public function __construct(Admission $admission)
    {
        $this->admission = $admission;
    }

    public function build()
    {
        return $this->subject('Update on your Admission Application - ' . env('SCHOOL_NAME'))
                    ->view('emails.admission_status');
    }
}