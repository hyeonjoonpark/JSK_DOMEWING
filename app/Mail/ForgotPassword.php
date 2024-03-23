<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ForgotPassword extends Mailable
{
    use Queueable, SerializesModels;
    public $name;
    public $tempPassword;
    public function __construct($data)
    {
        $this->name = $data['name'];
        $this->tempPassword = $data['tempPassword'];
    }
    public function build()
    {
        return $this->view('emails.forgot_password')
            ->subject('임시 비밀번호 발급');
    }
}
