<?php

namespace App\Services;

use Resend\Resend;

class ResendMailService
{
    public static function send($to, $subject, $html)
    {
        $resend = Resend::client(config('services.resend.key'));

        return $resend->emails->send([
            'from' => config('mail.from.name') . ' <' . config('mail.from.address') . '>',
            'to' => [$to],
            'subject' => $subject,
            'html' => $html,
        ]);
    }
}
