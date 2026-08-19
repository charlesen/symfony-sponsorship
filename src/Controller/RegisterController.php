<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserForm;
use App\Service\Brevo;
use App\Service\ReferralService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/{_locale}')]
final class RegisterController extends AbstractController
{
    #[Route('/register', name: 'register')]
    public function index(
        Request $request,
        TranslatorInterface $translator,
        EntityManagerInterface $entityManager,
        Brevo $brevo,
        ReferralService $referralService
    ): Response {
        if ($this->getUser()) {
            return $this->redirectToRoute('dashboard_index');
        }

        $user = new User();
        $user->setLocale($request->getLocale());
        
        $form = $this->createForm(UserForm::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($user);
            $entityManager->flush();

            // Process referral attribution (cookie, session, or ?ref= parameter)
            $referrer = $referralService->processReferral($user, $request);

            // Add user to Brevo CRM
            try {
                $brevo->addContact(
                    $user->getEmail(),
                    [
                        'FIRSTNAME' => $user->getFirstname(),
                        'LASTNAME' => $user->getLastname(),
                        'REFERRER_CODE' => $user->getReferrerCode(),
                        'REFERRED_BY' => $referrer ? $referrer->getEmail() : null,
                    ]
                );
            } catch (\Exception $e) {
                // Log and continue
            }

            if ($referrer) {
                $this->addFlash('success', $translator->trans('Account created with bonus welcome points from your referral! Check your email for magic login link.'));
            } else {
                $this->addFlash('success', $translator->trans('You have been successfully registered! Please log in with your email.'));
            }

            return $this->redirectToRoute('login');
        }

        return $this->render('register/index.html.twig', [
            'form' => $form,
        ]);
    }
}
