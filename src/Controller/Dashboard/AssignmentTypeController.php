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
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

#[Route('/{_locale}/dashboard/assignment/type', defaults: ['_locale' => 'en'], requirements: ['_locale' => 'en|fr'], name: 'dashboard_assignment_type_')]
final class AssignmentTypeController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly AssignmentTypeRepository $assignmentTypeRepository
    ) {
    }

    #[Route('', name: 'index', methods: ['GET'])]
    public function index(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        
        $assignmentTypes = $this->assignmentTypeRepository->findAllOrderedByTitle();
        
        return $this->render('dashboard/assignment_type/index.html.twig', [
            'assignment_types' => $assignmentTypes,
        ]);
    }

    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        
        $assignmentType = new AssignmentType();
        $form = $this->createForm(AssignmentTypeForm::class, $assignmentType);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->entityManager->persist($assignmentType);
                $this->entityManager->flush();
                
                $this->addFlash('success', 'assignment_type.flash.created_successfully');
                
                return $this->redirectToRoute('dashboard_assignment_type_show', [
                    'id' => $assignmentType->getId(),
                ], Response::HTTP_SEE_OTHER);
            } catch (\Exception $e) {
                $this->addFlash('error', 'flash.error.occurred');
                // Log the error here if you have a logger
            }
        }

        return $this->render('dashboard/assignment_type/new.html.twig', [
            'assignment_type' => $assignmentType,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(int $id): Response
    {
        $assignmentType = $this->assignmentTypeRepository->find($id);
        
        if (!$assignmentType) {
            throw $this->createNotFoundException('assignment_type.not_found');
        }

        return $this->render('dashboard/assignment_type/show.html.twig', [
            'assignment_type' => $assignmentType,
        ]);
    }

    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, int $id): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        
        $assignmentType = $this->assignmentTypeRepository->find($id);
        
        if (!$assignmentType) {
            throw $this->createNotFoundException('assignment_type.not_found');
        }
        
        $form = $this->createForm(AssignmentTypeForm::class, $assignmentType);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->entityManager->flush();
                
                $this->addFlash('success', 'assignment_type.flash.updated_successfully');
                
                return $this->redirectToRoute('dashboard_assignment_type_show', [
                    'id' => $assignmentType->getId(),
                ], Response::HTTP_SEE_OTHER);
            } catch (\Exception $e) {
                $this->addFlash('error', 'flash.error.occurred');
                // Log the error here if you have a logger
            }
        }

        return $this->render('dashboard/assignment_type/edit.html.twig', [
            'assignment_type' => $assignmentType,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'delete', methods: ['POST'])]
    public function delete(Request $request, int $id): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        
        $assignmentType = $this->assignmentTypeRepository->find($id);
        
        if (!$assignmentType) {
            throw $this->createNotFoundException('assignment_type.not_found');
        }
        
        if ($this->isCsrfTokenValid('delete' . $assignmentType->getId(), $request->getPayload()->getString('_token'))) {
            try {
                if ($assignmentType->getAssignments()->count() > 0) {
                    $this->addFlash('error', 'assignment_type.flash.cannot_delete_has_assignments');
                    return $this->redirectToRoute('dashboard_assignment_type_show', ['id' => $id]);
                }
                
                $this->entityManager->remove($assignmentType);
                $this->entityManager->flush();
                
                $this->addFlash('success', 'assignment_type.flash.deleted_successfully');
            } catch (\Exception $e) {
                $this->addFlash('error', 'flash.error.occurred');
                // Log the error here if you have a logger
            }
        } else {
            $this->addFlash('error', 'Invalid CSRF token');
        }

        return $this->redirectToRoute('dashboard_assignment_type_index', [], Response::HTTP_SEE_OTHER);
    }
}
