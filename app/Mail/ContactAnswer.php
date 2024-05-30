<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ContactAnswer extends Mailable
{
    use Queueable, SerializesModels;

    public string $name;
    public string $question;
    public string $answer;

    public function __construct($contact)
    {
        $this->name = (string) $contact->name;
        $this->question = (string) $contact->message;
        $this->answer = (string) $contact->answer;
    }

    public function build()
    {
        return $this->view('emails.contact_answer')
            ->subject('문의 내용에 대한 답변입니다')
            ->with([
                'name' => $this->name,
                'question' => $this->question,
                'answer' => $this->answer,
            ]);
    }
}
