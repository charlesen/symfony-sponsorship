<?php

namespace App\Controller\Dashboard;

use App\Entity\Assignment;
use App\Form\AssignmentForm;
use App\Repository\AssignmentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/{_locale}/dashboard/assignment', name: 'dashboard_assignment_')]
final class AssignmentController extends AbstractController
{
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(AssignmentRepository $assignmentRepository): Response
    {
        return $this->render('dashboard/assignment/index.html.twig', [
            'assignments' => $assignmentRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $assignment = new Assignment();
        $form = $this->createForm(AssignmentForm::class, $assignment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($assignment);
            $entityManager->flush();

            return $this->redirectToRoute('dashboard_assignment_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('dashboard/assignment/new.html.twig', [
            'assignment' => $assignment,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(Assignment $assignment): Response
    {
        return $this->render('dashboard/assignment/show.html.twig', [
            'assignment' => $assignment,
        ]);
    }

    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Assignment $assignment, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(AssignmentForm::class, $assignment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('dashboard_assignment_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('dashboard/assignment/edit.html.twig', [
            'assignment' => $assignment,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'delete', methods: ['POST'])]
    public function delete(Request $request, Assignment $assignment, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $assignment->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($assignment);
            $entityManager->flush();
        }

        return $this->redirectToRoute('dashboard_assignment_index', [], Response::HTTP_SEE_OTHER);
    }
}
