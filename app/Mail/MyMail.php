<?php

declare(strict_types=1);

namespace App\Mail;

class MyMail
{
    public function send($to, $subject, $body)
    {
        // integrate with Mailer
        return true;
    }
}
