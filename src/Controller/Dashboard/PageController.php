<?php

namespace App\Controller\Dashboard;

use App\Entity\Page;
use App\Form\PageForm;
use App\Repository\PageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/{_locale}/dashboard/page', defaults: ['_locale' => 'en'], requirements: ['_locale' => 'en|fr'], name: 'dashboard_page_')]
final class PageController extends AbstractController
{
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(PageRepository $pageRepository): Response
    {
        return $this->render('dashboard/page/index.html.twig', [
            'pages' => $pageRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $page = new Page();
        $form = $this->createForm(PageForm::class, $page);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($page);

            $page->generateUniqueSlug(
                $page->getTitle(),
                fn(string $slug) => (bool) $entityManager->getRepository(Page::class)->findOneBy(['slug' => $slug])
            );
            $entityManager->flush();

            return $this->redirectToRoute('dashboard_page_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('dashboard/page/new.html.twig', [
            'page' => $page,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(Page $page): Response
    {
        return $this->render('dashboard/page/show.html.twig', [
            'page' => $page,
        ]);
    }

    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Page $page, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(PageForm::class, $page);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('dashboard_page_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('dashboard/page/edit.html.twig', [
            'page' => $page,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'delete', methods: ['POST'])]
    public function delete(Request $request, Page $page, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $page->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($page);
            $entityManager->flush();
        }

        return $this->redirectToRoute('dashboard_page_index', [], Response::HTTP_SEE_OTHER);
    }
}
