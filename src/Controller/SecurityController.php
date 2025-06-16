<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Notifier\NotifierInterface;
use Symfony\Component\Notifier\Recipient\Recipient;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\LoginLink\LoginLinkHandlerInterface;
use App\Notifier\CustomLoginLinkNotification;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/{_locale}')]
final class SecurityController extends AbstractController
{
    public function __construct(
        private readonly LoggerInterface $logger
    ) {}
    #[Route('/login_check', name: 'login_check')]
    public function index(): Response
    {
        throw new \LogicException('This code should never be reached');
    }

    #[Route('/login', name: 'login')]
    public function requestLoginLink(
        NotifierInterface $notifier,
        LoginLinkHandlerInterface $loginLinkHandler,
        UserRepository $userRepository,
        Request $request,
        TranslatorInterface $translator
    ): Response {

        if ($request->isMethod('POST')) {
            $email = $request->getPayload()->get('email');
            $user = $userRepository->findOneBy(['email' => $email]);

            if (!$user) {
                $this->addFlash('error', $translator->trans('It looks like you do not have an account'));
                return $this->redirectToRoute('register');
            }

            $loginLinkDetails = $loginLinkHandler->createLoginLink($user);

            $userLocale = $user->getLocale() ?? $request->getLocale();

            // create a notification based on the login link details
            $notification = new CustomLoginLinkNotification(
                $loginLinkDetails,
                $translator->trans('Application login link'), // email subject
                ['email'],
                [
                    'userLocale' => $userLocale,
                    'from' => $this->getParameter('app.email.from'),
                ]
            );
            // create a recipient for this user
            $recipient = new Recipient($user->getEmail());

            // send the notification to the user
            try {
                $notifier->send($notification, $recipient);
                // Si on arrive ici, l'envoi a réussi
                $this->addFlash('success', $translator->trans('Login link sent ! Please check your email'));
                return $this->redirectToRoute('login');
            } catch (TransportExceptionInterface $e) {
                // En cas d'erreur, on log l'erreur et on affiche un message à l'utilisateur
                $this->addFlash('error', $translator->trans('Login link could not be sent!'));
                // On peut logger l'erreur pour le débogage
                $this->logger->error('Failed to send login link to {email}: {error}', [
                    'email' => $email,
                    'error' => $e->getMessage(),
                    'exception' => $e
                ]);

                return $this->redirectToRoute('login');
            }
        }

        return $this->render('security/login.html.twig');
    }

    /**
     * Logout
     */
    #[Route('/logout', name: 'logout')]
    public function logout(): Response
    {
        throw new \LogicException('This code should never be reached');
    }
}
