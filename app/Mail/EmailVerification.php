<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EmailVerification extends Mailable
{
    use Queueable, SerializesModels;

    public $name;
    public $token;

    public function __construct($partner)
    {
        $this->name = $partner['name'];
        $this->token = $partner['token'];
    }

    public function build()
    {
        return $this->view('emails.email_verification')
            ->subject('셀윙 파트너스에 가입해주셔서 진심으로 감사드립니다');
    }
}
