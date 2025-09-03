<?php

namespace App\Mail;

use App\Models\Message;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NouveauMessageNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $message;
    public $sender;

    public function __construct(Message $message, User $sender)
    {
        $this->message = $message;
        $this->sender = $sender;
    }

    public function build()
    {
        return $this->subject('Nouveau message reçu')
                    ->view('emails.nouveau_message');
    }
}

