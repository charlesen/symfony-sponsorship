<?php

namespace App\Service;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\Mailer\MailerInterface;
use Psr\Log\LoggerInterface;

class Mailer
{
    public function __construct(
        private MailerInterface $mailer,
        private readonly ContainerBagInterface $containerBag,
        private readonly LoggerInterface $logger
    ) {}

    public function sendEmail(string $to, string $subject, array $context, string $template = 'emails/assignment_email.html.twig'): void
    {
        try {
            $email = (new TemplatedEmail())
                ->from($this->containerBag->get('app.email.from'))
                ->to($to)
                ->subject($subject)
                ->htmlTemplate($template)
                ->context($context);

            $this->mailer->send($email);
        } catch (\Exception $e) {
            $this->logger->error('Failed to send email', [
                'exception' => $e,
            ]);
        }
    }
}
