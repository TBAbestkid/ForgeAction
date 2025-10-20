<?php

namespace App\Services;

use App\Services\BrevoMailService;
use Illuminate\Mail\Mailable;

class ApiMailer
{
    protected $brevo;

    public function __construct()
    {
        $this->brevo = new BrevoMailService();
    }

    /**
     * Envia um Mailable via API Brevo
     *
     * @param Mailable $mailable
     * @return bool
     */
    public function send(Mailable $mailable): bool
    {
        // Renderiza o HTML do Mailable
        $html = $mailable->build()->render();

        // Pega os destinatários do Mailable
        $tos = $mailable->to ?? [];

        if (empty($tos)) {
            throw new \Exception("Nenhum destinatário definido no Mailable.");
        }

        $success = true;

        foreach ($tos as $to) {
            $sent = $this->brevo->send(
                $to['address'],
                $to['name'] ?? $to['address'],
                $mailable->subject ?? 'Sem assunto',
                $html
            );

            if (!$sent) $success = false;
        }

        return $success;
    }
}
