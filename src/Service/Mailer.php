<?php

namespace App\Service;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;

class Mailer
{
    public function __construct(private MailerInterface $mailer) {}

    public function sendEmail(string $to, string $subject, array $context, string $template = 'emails/assignment_email.html.twig'): void
    {
        $email = (new TemplatedEmail())
            ->from('no-reply@email.edounze.com')
            ->to($to)
            ->subject($subject)
            ->htmlTemplate($template)
            ->context($context);

        $this->mailer->send($email);
    }
}
