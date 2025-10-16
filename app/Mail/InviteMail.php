<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class InviteMail extends Mailable
{
    use Queueable, SerializesModels;

    public $remetente;
    public $sala;
    public $link;

    public function __construct($remetente, $sala, $link)
    {
        $this->remetente = $remetente;
        $this->sala = $sala;
        $this->link = $link;
    }

    public function build()
    {
        return $this->subject("Você foi convidado para a sala {$this->sala}")
                    ->view('emails.invite')
                    ->with([
                        'remetente' => $this->remetente,
                        'sala' => $this->sala,
                        'link' => $this->link,
                    ]);
    }
}
