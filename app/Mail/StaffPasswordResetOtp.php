<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;

class StaffPasswordResetOtp extends Mailable
{
    public function __construct(public string $code) {}

    public function build()
    {
        return $this->subject('Your staff password reset code')->view('emails.staff_password_reset_otp');
    }
}
