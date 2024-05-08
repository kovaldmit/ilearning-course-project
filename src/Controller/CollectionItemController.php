<?php

namespace App\Controller;

use App\Entity\CollectionContainer;
use App\Entity\CollectionCustomFieldValue;
use App\Entity\CollectionItem;
use App\Form\CollectionItemType;
use App\Repository\CollectionItemRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/item')]
class CollectionItemController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/', name: 'collection_item_index', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function index(CollectionItemRepository $collectionItemRepository): Response
    {
        $items = $collectionItemRepository->findAll();

        return $this->render('collection_item/index.html.twig', [
            'items' => $items,
        ]);
    }

    #[Route('/new/{id}', name: 'collection_item_new')]
    public function new(
        CollectionContainer $container,
        Request $request,
    ): Response {
        $this->denyAccessUnlessGranted('new', $container);

        $item = new CollectionItem();
        $item->setCollectionContainer($container);

        $form = $this->createForm(CollectionItemType::class, $item);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($item);
            $this->entityManager->flush();

            $customFields = $container->getCustomFields();
            foreach ($customFields as $customField) {
                $fieldValue = $form->get('customField_' . $customField->getId())->getData();
                if ($fieldValue !== null) {
                    $customFieldValue = new CollectionCustomFieldValue();
                    $customFieldValue->setCustomField($customField);
                    $customFieldValue->setItem($item);
                    $customFieldValue->setValue($fieldValue);
                    $this->entityManager->persist($customFieldValue);
                }
            }

            $this->entityManager->flush();

            return $this->redirectToRoute('collection_container_show', ['id' => $container->getId()]);
        }

        return $this->render('collection_item/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'collection_item_show', methods: ['GET'])]
    public function show(CollectionItem $item): Response
    {
        return $this->render('collection_item/show.html.twig', [
            'item' => $item,
        ]);
    }

    #[Route('/{id}/edit', name: 'collection_item_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, CollectionItem $item): Response
    {
        $container = $item->getCollectionContainer();
        $this->denyAccessUnlessGranted('edit', $container);

        $form = $this->createForm(CollectionItemType::class, $item);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            foreach ($item->getCollectionContainer()->getCustomFields() as $customField) {
                $fieldValue = $form->get('customField_' . $customField->getId())->getData();
                $customFieldValue = $this->entityManager->getRepository(CollectionCustomFieldValue::class)->findOneBy([
                    'item' => $item,
                    'customField' => $customField,
                ]);
                if (!$customFieldValue) {
                    $customFieldValue = new CollectionCustomFieldValue();
                    $customFieldValue->setItem($item);
                    $customFieldValue->setCustomField($customField);
                }
                $customFieldValue->setValue($fieldValue);
                $this->entityManager->persist($customFieldValue);
            }
            $this->entityManager->flush();

            return $this->redirectToRoute('collection_item_show', ['id' => $item->getId()]);
        }

        return $this->render('collection_item/edit.html.twig', [
            'item' => $item,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/delete', name: 'collection_item_delete', methods: ['POST'])]
    public function delete(Request $request, CollectionItem $item): Response
    {
        $container = $item->getCollectionContainer();
        $this->denyAccessUnlessGranted('delete', $container);

        if ($this->isCsrfTokenValid('delete' . $item->getId(), $request->request->get('_token'))) {
            $this->entityManager->remove($item);
            $this->entityManager->flush();
        }

        return $this->redirectToRoute('collection_container_show', ['id' => $container->getId()]);
    }
}
