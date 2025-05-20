<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mailer\Mailer;

#[AsCommand(
    name: 'app:send-test-email',
    description: 'Send a test email',
)]
class SendTestEmailCommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // Créer un transport SMTP direct sans passer par Messenger
        $dsn = 'smtp://mailpit:1025';
        $transport = Transport::fromDsn($dsn);
        $mailer = new Mailer($transport);

        $email = (new Email())
            ->from('noreply@example.com')
            ->to('test@example.com')
            ->subject('Test Email from Symfony')
            ->text('This is a test email sent from Symfony with Mailpit.')
            ->html('<p>This is a <strong>test email</strong> sent from Symfony with Mailpit.</p>');

        try {
            $mailer->send($email);
            $output->writeln('Test email sent successfully!');
            $output->writeln('Check your Mailpit interface at http://localhost:8025');
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $output->writeln(sprintf('Error sending email: %s', $e->getMessage()));
            return Command::FAILURE;
        }
    }
}
