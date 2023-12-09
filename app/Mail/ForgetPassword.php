<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ForgetPassword extends Mailable
{
    use Queueable, SerializesModels;

    public $name;
    public $remember_token;

    public function __construct($data)
    {
        $this->name = $data['name'];
        $this->remember_token = $data['remember_token'];
    }

    public function build()
    {
        return $this->view('emails.forget_password')
            ->subject('Domewing - Reset Password');
    }
}
