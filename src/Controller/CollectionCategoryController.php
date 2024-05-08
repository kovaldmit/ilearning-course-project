<?php

namespace App\Controller;

use App\Entity\CollectionCategory;
use App\Form\CollectionCategoryType;
use App\Repository\CollectionCategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/category')]
class CollectionCategoryController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/', name: 'collection_category_index', methods: ['GET'])]
    public function index(CollectionCategoryRepository $collectionCategoryRepository): Response
    {
        $categories = $collectionCategoryRepository->findAll();

        return $this->render('collection_category/index.html.twig', [
            'categories' => $categories,
        ]);
    }

    #[Route('/new', name: 'collection_category_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function new(Request $request): Response
    {
        $category = new CollectionCategory();
        $form = $this->createForm(CollectionCategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($category);
            $this->entityManager->flush();

            return $this->redirectToRoute('collection_category_index');
        }

        return $this->render('collection_category/new.html.twig', [
            'category' => $category,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'collection_category_show', methods: ['GET'])]
    public function show(CollectionCategory $category): Response
    {
        return $this->render('collection_category/show.html.twig', [
            'category' => $category,
        ]);
    }

    #[Route('/{id}/edit', name: 'collection_category_edit', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function edit(Request $request, CollectionCategory $category): Response
    {
        $form = $this->createForm(CollectionCategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            return $this->redirectToRoute('collection_category_index');
        }

        return $this->render('collection_category/edit.html.twig', [
            'category' => $category,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/delete', name: 'collection_category_delete', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(Request $request, CollectionCategory $category): Response
    {
        if ($this->isCsrfTokenValid('delete' . $category->getId(), $request->request->get('_token'))) {
            $this->entityManager->remove($category);
            $this->entityManager->flush();
        }

        return $this->redirectToRoute('collection_category_index');
    }
}
