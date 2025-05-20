<?php

namespace App\Tests\Integration\Command;

use App\Command\SendTestEmailCommand;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

/**
 * Test d'intégration pour la commande d'envoi d'email de test
 * 
 * Ce test vérifie que la commande peut être exécutée avec succès
 * et qu'elle tente d'envoyer un email avec les bonnes propriétés.
 */
class SendTestEmailCommandTest extends TestCase
{
    public function testExecute(): void
    {
        // Crée un mock pour le mailer
        $mailer = $this->createMock(MailerInterface::class);
        
        // Configure le mock pour qu'il vérifie que la méthode send est appelée
        $mailer->expects($this->once())
            ->method('send')
            ->willReturnCallback(function (Email $email) {
                $this->assertEquals('Test Email from Symfony', $email->getSubject());
                
                // Vérifie l'expéditeur
                $from = $email->getFrom();
                $this->assertCount(1, $from);
                $this->assertEquals('noreply@example.com', $from[0]->getAddress());
                
                // Vérifie le destinataire
                $to = $email->getTo();
                $this->assertCount(1, $to);
                $this->assertEquals('test@example.com', $to[0]->getAddress());
                
                // Vérifie le contenu de l'email
                $textBody = $email->getTextBody();
                $this->assertIsString($textBody);
                $this->assertStringContainsString(
                    'This is a test email sent from Symfony with Mailpit.',
                    $textBody
                );
                
                $htmlBody = $email->getHtmlBody();
                $this->assertIsString($htmlBody);
                $this->assertStringContainsString(
                    '<p>This is a <strong>test email</strong> sent from Symfony with Mailpit.</p>',
                    $htmlBody
                );
            });
        
        // Crée une instance de la commande avec le mock du mailer
        // Utilisation d'une assertion de type pour éviter les erreurs de type
        /** @var MailerInterface $mailer */
        $command = new SendTestEmailCommand($mailer);
        
        // Configure l'application de console
        $application = new Application();
        $application->add($command);
        
        // Récupère la commande et la configure avec l'application
        $command = $application->find('app:send-test-email');
        $commandTester = new CommandTester($command);

        // Exécute la commande
        $commandTester->execute([]);

        // Vérifie la sortie de la commande
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('Test email sent successfully!', $output);
        $this->assertStringContainsString('Check your Mailpit interface', $output);
        
        // Vérifie que la commande s'est terminée avec succès (code 0)
        $this->assertEquals(0, $commandTester->getStatusCode());
    }
}
