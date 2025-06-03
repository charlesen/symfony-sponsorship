<?php

namespace App\Notifier;

use Symfony\Component\Notifier\Message\EmailMessage;
use Symfony\Component\Notifier\Recipient\EmailRecipientInterface;
use Symfony\Component\Security\Http\LoginLink\LoginLinkDetails;
use Symfony\Component\Security\Http\LoginLink\LoginLinkNotification;
use Symfony\Contracts\Translation\TranslatorInterface;

class CustomLoginLinkNotification extends LoginLinkNotification
{
    public function __construct(
        private LoginLinkDetails $loginLinkDetails,
        private string $subject,
        array $channels = [],
        private array $params = []
    ) {
        parent::__construct($loginLinkDetails, $subject, $channels);
    }

    public function asEmailMessage(EmailRecipientInterface $recipient, ?string $transport = null): ?EmailMessage
    {
        $emailMessage = parent::asEmailMessage($recipient, $transport);

        // get the NotificationEmail object and override the template
        $email = $emailMessage->getMessage();
        $context = [
            ...$email->getContext(),
            ...$this->params,
            'recipient' => $recipient,
            'loginLinkDetails' => $this->loginLinkDetails,
            'subject' => $this->subject,
        ];

        $email->from('charles@edounze.com');

        $email->htmlTemplate('security/emails/custom_login_link_email.html.twig')
            ->context($context);

        return $emailMessage;
    }

    public function translate(TranslatorInterface $translator, $textToTranslate)
    {
        return $translator->trans($textToTranslate);
    }
}
