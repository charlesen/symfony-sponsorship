<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mailer\Mailer as SymfonyMailer;

#[AsCommand(
    name: 'app:send-test-email',
    description: 'Send a test email',
)]
class SendTestEmailCommand extends Command
{
    private $mailer;

    public function __construct(MailerInterface $mailer = null)
    {
        parent::__construct();
        $this->mailer = $mailer;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // Si aucun mailer n'est injecté, on en crée un nouveau
        if ($this->mailer === null) {
            $dsn = 'smtp://mailpit:1025';
            $transport = Transport::fromDsn($dsn);
            $this->mailer = new SymfonyMailer($transport);
        }

        $email = (new Email())
            ->from('noreply@example.com')
            ->to('test@example.com')
            ->subject('Test Email from Symfony')
            ->text('This is a test email sent from Symfony with Mailpit.')
            ->html('<p>This is a <strong>test email</strong> sent from Symfony with Mailpit.</p>');

        try {
            $this->mailer->send($email);
            $output->writeln('Test email sent successfully!');
            $output->writeln('Check your Mailpit interface at http://localhost:8025');
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $output->writeln(sprintf('Error sending email: %s', $e->getMessage()));
            return Command::FAILURE;
        }
    }
}
