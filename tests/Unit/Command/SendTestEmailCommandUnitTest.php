<?php

namespace App\Tests\Unit\Command;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use App\Command\SendTestEmailCommand;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

/**
 * Test unitaire pour la commande d'envoi d'email de test
 */
class SendTestEmailCommandUnitTest extends TestCase
{
    /** @var MailerInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $mailer;
    
    /** @var CommandTester */
    private $commandTester;

    protected function setUp(): void
    {
        // Crée un mock pour le mailer
        $this->mailer = $this->createMock(MailerInterface::class);
        
        // Crée une instance de la commande avec le mock du mailer
        // On utilise une assertion de type pour forcer le typage correct
        /** @var MailerInterface $mailer */
        $mailer = $this->mailer;
        $command = new SendTestEmailCommand($mailer);
        
        // Configure l'application et la commande pour le test
        $application = new Application();
        $application->add($command);
        
        // Crée le testeur de commande
        $commandInApp = $application->find('app:send-test-email');
        $this->commandTester = new CommandTester($commandInApp);
    }

    public function testExecute(): void
    {
        // Configure le mock pour qu'il vérifie que la méthode send est appelée avec les bons paramètres
        $this->mailer->expects($this->once())
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
        
        // Exécute la commande
        $this->commandTester->execute([]);
        
        // Vérifie la sortie
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Test email sent successfully!', $output);
        $this->assertStringContainsString('Check your Mailpit interface', $output);
        
        // Vérifie que la commande s'est terminée avec succès (code 0)
        $this->assertEquals(0, $this->commandTester->getStatusCode());
    }
}
