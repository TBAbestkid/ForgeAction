<?php

namespace App\Services;

use App\Services\ApiMailer;
use Illuminate\Mail\Mailable;

class MailerAdapter
{
    protected $apiMailer;

    public function __construct()
    {
        $this->apiMailer = new ApiMailer();
    }

    /**
     * Envia um Mailable usando o ApiMailer.
     *
     * @param Mailable $mailable
     * @return bool
     */
    public function send(Mailable $mailable): bool
    {
        return $this->apiMailer->send($mailable);
    }
}
