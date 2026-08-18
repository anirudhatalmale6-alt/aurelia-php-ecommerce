<?php
namespace App\Core;

/**
 * Outbound mail.
 *
 * The 'log' driver writes the rendered message to storage/logs/mail.log, which
 * keeps development and demo environments from sending real email. Switch
 * MAIL_DRIVER to 'mail' (or wire an SMTP transport) in production.
 */
class Mailer
{
    public static function send(string $to, string $subject, string $template, array $data = []): bool
    {
        $body = View::partial('emails/' . $template, $data + ['subject' => $subject]);

        $from    = App::config('mail.from');
        $name    = App::config('mail.name');
        $headers = [
            'MIME-Version: 1.0',
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . sprintf('%s <%s>', $name, $from),
            'Reply-To: ' . $from,
        ];

        if (App::config('mail.driver') === 'mail') {
            return mail($to, $subject, $body, implode("\r\n", $headers));
        }

        $log = App::config('app.root') . '/storage/logs/mail.log';
        $entry = sprintf(
            "==== %s ====\nTo: %s\nSubject: %s\n%s\n\n%s\n\n",
            date('Y-m-d H:i:s'),
            $to,
            $subject,
            implode("\n", $headers),
            $body
        );
        file_put_contents($log, $entry, FILE_APPEND);
        return true;
    }
}
