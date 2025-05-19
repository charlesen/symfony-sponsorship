<?php

namespace App\Controller\Dashboard;

use App\Entity\AssignmentType;
use App\Form\AssignmentTypeForm;
use App\Repository\AssignmentTypeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/{_locale}/dashboard/assignment/type', defaults: ['_locale' => 'en'], requirements: ['_locale' => 'en|fr'], name: 'dashboard_assignment_type_')]
final class AssignmentTypeController extends AbstractController
{
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(AssignmentTypeRepository $assignmentTypeRepository): Response
    {
        return $this->render('dashboard/assignment_type/index.html.twig', [
            'assignment_types' => $assignmentTypeRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $assignmentType = new AssignmentType();
        $form = $this->createForm(AssignmentTypeForm::class, $assignmentType);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($assignmentType);
            $entityManager->flush();

            return $this->redirectToRoute('dashboard_assignment_type_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('dashboard/assignment_type/new.html.twig', [
            'assignment_type' => $assignmentType,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(AssignmentType $assignmentType): Response
    {
        return $this->render('dashboard/assignment_type/show.html.twig', [
            'assignment_type' => $assignmentType,
        ]);
    }

    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, AssignmentType $assignmentType, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(AssignmentTypeForm::class, $assignmentType);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('dashboard_assignment_type_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('dashboard/assignment_type/edit.html.twig', [
            'assignment_type' => $assignmentType,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'delete', methods: ['POST'])]
    public function delete(Request $request, AssignmentType $assignmentType, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $assignmentType->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($assignmentType);
            $entityManager->flush();
        }

        return $this->redirectToRoute('dashboard_assignment_type_index', [], Response::HTTP_SEE_OTHER);
    }
}
