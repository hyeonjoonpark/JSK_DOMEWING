<?php
namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EmailVerificationMember extends Mailable
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
        return $this->view('emails.email_verification_member')
            ->subject('Thank you very much for joining DomeWing.');
    }
}
