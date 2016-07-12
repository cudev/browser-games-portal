<?php

namespace Ludos\Schedule\Tasks;

use Cudev\OrdinaryMail\Letter;
use Cudev\OrdinaryMail\Mailer;

class SendLetter
{
    private $mailer;
    private $letter;

    public function __construct(Mailer $mailer, Letter $letter)
    {
        $this->mailer = $mailer;
        $this->letter = $letter;
    }

    public function __invoke()
    {
        if (!$this->mailer->send($this->letter)) {
            error_log(printf('Cannot send a letter with subject: %s', $this->letter->getSubject()));
        }
    }
}
