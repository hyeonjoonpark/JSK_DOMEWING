<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EmailVerification extends Mailable
{
    use Queueable, SerializesModels;

    public $name;
    public $remember_token;

    public function __construct($user)
    {
        $this->name = $user['name'];
        $this->remember_token = $user['remember_token'];
    }

    public function build()
    {
        return $this->view('emails.email_verification')
            ->subject('도매윙에 가입해주셔서 진심으로 감사드립니다');
    }
}
