<?php

namespace App\Twig\Components;

use App\Service\Brevo;
use App\Service\Mailer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\UX\LiveComponent\ValidatableComponentTrait;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Composant Symfony UX Live Component pour gérer l'envoi d'invitations par e-mail.
 * Permet à un utilisateur d'ajouter plusieurs contacts et d'envoyer des invitations par e-mail.
 */
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

    /**
     * Liste des contacts invités.
     * Chaque contact est un tableau associatif contenant 'firstName' et 'email'.
     *
     * @var array
     */
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

    /**
     * Ajoute un nouveau bloc de saisie pour un contact.
     * Cette méthode est appelée lors du clic sur le bouton "Ajouter un contact".
     */
    #[LiveAction]
    public function addContact(): void
    {
        $this->contacts[] = ['firstName' => '', 'email' => ''];
    }

    /**
     * Supprime un bloc de saisie pour un contact.
     * Cette méthode est appelée lors du clic sur le bouton "Supprimer".
     */
    #[LiveAction]
    public function removeContact(array $data): void
    {
        $index = $data['index'] ?? null;
        if (null !== $index && isset($this->contacts[$index])) {
            unset($this->contacts[$index]);
            $this->contacts = array_values($this->contacts);
        }

        if (empty($this->contacts)) {
            $this->addContact();
        }
    }

    /**
     * Envoie des invitations par e-mail à tous les contacts.
     * Cette méthode est appelée lors du clic sur le bouton "Envoyer les invitations".
     */
    #[LiveAction]
    public function sendInvitations()
    {
        // Validation des données
        $this->validate();
        foreach ($this->contacts as $contact) {

            // Send email based on template            
            $this->mailer->sendEmail(
                $contact['email'],
                $this->translator->trans('AssignmentEmail.email.subject'),
                [
                    'firstName' => $contact['firstName'],
                    'url' => $this->urlGenerator->generate('register', [], UrlGeneratorInterface::ABSOLUTE_URL),
                ],
                'emails/assignment_email.html.twig'
            );

            // Add contact to Brevo
            $this->brevo->addContact(
                $contact['email'],
                [
                    'firstName' => $contact['firstName'],
                ]
            );
        }

        $this->addFlash('success', $this->translator->trans('AssignmentEmail.email.success'));

        return $this->redirectToRoute('dashboard_index');
    }
}
