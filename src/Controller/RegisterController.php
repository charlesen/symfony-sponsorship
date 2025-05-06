<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Form\UserForm;
use Symfony\Contracts\Translation\TranslatorInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User;

#[Route('/{_locale}')]
final class RegisterController extends AbstractController
{
    #[Route('/register', name: 'register')]
    public function index(Request $request, TranslatorInterface $translator, EntityManagerInterface $entityManager): Response
    {
        $user = new User();
        $form = $this->createForm(UserForm::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Generate a random referrer code
            $referrerCode = bin2hex(random_bytes(4));
            $user->setReferrerCode($referrerCode);
            $entityManager->persist($user);
            $entityManager->flush();
            $this->addFlash('success', $translator->trans('You have been successfully registered! Please log in.'));
            return $this->redirectToRoute('login');
        }

        return $this->render('register/index.html.twig', [
            'form' => $form,
        ]);
    }
}
