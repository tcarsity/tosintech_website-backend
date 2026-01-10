<?php

namespace App\Services;

use Resend\Resend;

class ResendMailService
{
    public static function send($to, $subject, $html)
    {
        $resend = Resend::client(env('RESEND_API_KEY'));

        return $resend->emails->send([
            'from' => env('MAIL_FROM_NAME') . ' <' . env('MAIL_FROM_ADDRESS') . '>',
            'to' => [$to],
            'subject' => $subject,
            'html' => $html,
        ]);
    }
}
