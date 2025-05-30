<?php

namespace App\Controller\Dashboard;

use App\Entity\Assignment;
use App\Form\AssignmentFormType;
use App\Repository\AssignmentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/{_locale}/dashboard/assignments')]
class AssignmentController extends AbstractController
{
    #[Route('/', name: 'dashboard_assignment_index', methods: ['GET'])]
    public function index(AssignmentRepository $assignmentRepository): Response
    {
        return $this->render('dashboard/assignment/index.html.twig', [
            'assignments' => $assignmentRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'dashboard_assignment_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $assignment = new Assignment();
        $form = $this->createForm(AssignmentFormType::class, $assignment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($assignment);
            $entityManager->flush();

            $this->addFlash('success', 'Assignment créé avec succès.');
            return $this->redirectToRoute('dashboard_assignment_show', ['id' => $assignment->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('dashboard/assignment/new.html.twig', [
            'assignment' => $assignment,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'dashboard_assignment_show', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function show(Request $request, AssignmentRepository $assignmentRepository): Response
    {
        $id = $request->attributes->get('id');
        $assignment = $assignmentRepository->find($id);

        if (!$assignment) {
            $this->addFlash('error', 'general', 'Assignment non trouvé.');
            return $this->redirectToRoute('dashboard_assignment_index');
        }

        return $this->render('dashboard/assignment/show.html.twig', [
            'assignment' => $assignment,
        ]);
    }

    #[Route('/{id}/edit', name: 'dashboard_assignment_edit', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function edit(Request $request, Assignment $assignment, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(AssignmentFormType::class, $assignment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Assignment mis à jour avec succès.');
            return $this->redirectToRoute('dashboard_assignment_show', ['id' => $assignment->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('dashboard/assignment/edit.html.twig', [
            'assignment' => $assignment,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'dashboard_assignment_delete', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function delete(Request $request, Assignment $assignment, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $assignment->getId(), $request->request->get('_token'))) {
            $entityManager->remove($assignment);
            $entityManager->flush();
            $this->addFlash('success', 'Assignment supprimé avec succès.');
        }

        return $this->redirectToRoute('dashboard_assignment_index', [], Response::HTTP_SEE_OTHER);
    }
}
