<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class InviteEmail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public $invitation;

    public function __construct($invitation)
    {
        $this->invitation = $invitation;
    }

    public function build()
    {
        return $this->subject('You have been invited!')
                    ->view('emails.invite');
    }
}
