<?php

namespace App\Service;

use App\Entity\User;
use Symfony\Component\Notifier\Notification\Notification;
use Symfony\Component\Notifier\NotifierInterface;
use Symfony\Component\Notifier\Recipient\Recipient;

final class Notifier
{
    public function __construct(
        private readonly NotifierInterface $notifier,
    ) {}

    public function notify(User $user, string $content, string $subject = 'New Invoice'): void
    {
        $notification = (new Notification($subject, ['email']))
            ->content($content);

        // The receiver of the Notification
        $recipient = new Recipient(
            $user->getEmail(),
        );

        // Send the notification to the recipient
        $this->notifier->send($notification, $recipient);
    }
}
