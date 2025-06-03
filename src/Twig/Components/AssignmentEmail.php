<?php

namespace App\Twig\Components;

use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Email as EmailConstraint;

/**
 * Composant Symfony UX Live Component pour gérer l'envoi d'invitations par e-mail.
 * Permet à un utilisateur d'ajouter plusieurs contacts et d'envoyer des invitations par e-mail.
 */
#[AsLiveComponent('AssignmentEmail')]
class AssignmentEmail
{
    use DefaultActionTrait;

    /**
     * Liste des contacts invités.
     * Chaque contact est un tableau associatif contenant 'firstName' et 'email'.
     *
     * @var array
     */
    #[LiveProp(writable: true)]
    #[Assert\All([
        new NotBlank(message: 'Le prénom est requis.'),
        new EmailConstraint(message: 'L\'adresse e-mail n\'est pas valide.')
    ])]
    public array $contacts = [['firstName' => '', 'email' => '']];

    /**
     * Constructeur du composant.
     */
    public function __construct(private MailerInterface $mailer) {}

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
     * Envoie des invitations par e-mail à tous les contacts.
     * Cette méthode est appelée lors du clic sur le bouton "Envoyer les invitations".
     */
    #[LiveAction]
    public function sendInvitations(): void
    {
        foreach ($this->contacts as $contact) {
            $email = (new Email())
                ->from('no-reply@tonsite.com')
                ->to($contact['email'])
                ->subject('Rejoins notre communauté et gagne des goodies !')
                ->html('<p>Bonjour ' . $contact['firstName'] . ',</p><p>Nous t\'invitons à rejoindre notre communauté pour gagner des goodies en partageant notre livre. Inscris-toi dès maintenant !</p>');

            $this->mailer->send($email);
        }
    }
}
