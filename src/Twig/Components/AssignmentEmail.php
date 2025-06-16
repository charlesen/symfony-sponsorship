<?php

namespace App\Twig\Components;

use App\Service\Brevo;
use App\Service\Mailer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\UX\LiveComponent\ValidatableComponentTrait;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\UX\LiveComponent\Attribute\LiveArg;

#[AsLiveComponent('AssignmentEmail')]
class AssignmentEmail extends AbstractController
{
    use DefaultActionTrait;
    use ValidatableComponentTrait;

    public function __construct(
        private Mailer $mailer,
        private Brevo $brevo,
        private TranslatorInterface $translator,
        private UrlGeneratorInterface $urlGenerator
    ) {}

    #[LiveProp(writable: true)]
    #[Assert\All(
        new Assert\Collection(
            fields: [
                'firstName' => new Assert\NotBlank(message: 'Le prénom est requis.'),
                'email'     => [
                    new Assert\NotBlank(message: 'L\'adresse e-mail est requise.'),
                    new Assert\Email(message: 'L\'adresse e-mail "{{ value }}" n’est pas valide.'),
                ],
            ],
            allowMissingFields: true,
            allowExtraFields: true
        )
    )]
    public array $contacts = [['firstName' => '', 'email' => '']];

    #[LiveAction]
    public function addContact(): void
    {
        $this->contacts[] = ['firstName' => '', 'email' => ''];
    }

    #[LiveAction]
    public function removeContact(#[LiveArg] int $index): void
    {
        if (isset($this->contacts[$index])) {
            unset($this->contacts[$index]);
            $this->contacts = array_values($this->contacts);

            if (empty($this->contacts)) {
                $this->addContact();
            }
        }
    }

    #[LiveAction]
    public function updateContacts(#[LiveArg] array $contacts): void
    {
        $this->contacts = array_filter(
            $contacts,
            fn($c) => isset($c['email']) && filter_var($c['email'], FILTER_VALIDATE_EMAIL)
        );

        if (empty($this->contacts)) {
            $this->contacts = [['firstName' => '', 'email' => '']];
        }
    }

    #[LiveAction]
    public function addContacts(array $contacts): void
    {
        foreach ($contacts as $contact) {
            $contactExists = false;
            foreach ($this->contacts as $existingContact) {
                if (strtolower($existingContact['email']) === strtolower($contact['email'])) {
                    $contactExists = true;
                    break;
                }
            }

            if (!$contactExists) {
                $this->contacts[] = [
                    'firstName' => $contact['firstName'],
                    'email' => $contact['email']
                ];
            }
        }

        if (empty($this->contacts)) {
            $this->addContact();
        }
    }

    #[LiveAction]
    public function sendInvitations()
    {
        $this->validate();

        $validContacts = array_filter($this->contacts, fn($c) => !empty($c['email']) && !empty($c['firstName']));

        if (empty($validContacts)) {
            $this->addFlash('error', $this->translator->trans('Aucun contact valide à inviter.'));
            return;
        }

        $seenEmails = [];
        $successCount = 0;
        $errorCount = 0;

        foreach ($validContacts as $contact) {
            $email = strtolower($contact['email']);
            if (in_array($email, $seenEmails, true)) {
                continue;
            }
            $seenEmails[] = $email;

            try {
                $this->mailer->sendEmail(
                    $contact['email'],
                    $this->translator->trans('AssignmentEmail.email.subject'),
                    [
                        'firstName' => $contact['firstName'],
                        'url' => $this->urlGenerator->generate('register', [], UrlGeneratorInterface::ABSOLUTE_URL),
                    ],
                    'emails/assignment_email.html.twig'
                );

                $this->brevo->addContact(
                    $contact['email'],
                    [
                        'firstName' => $contact['firstName'],
                    ]
                );

                $successCount++;
            } catch (\Exception $e) {
                $errorCount++;
                error_log(sprintf('Error sending invitation to %s: %s', $contact['email'], $e->getMessage()));
            }
        }

        $this->addFlash('success', $this->translator->trans('AssignmentEmail.email.success'));
        return $this->redirectToRoute('dashboard_index');
    }
}
