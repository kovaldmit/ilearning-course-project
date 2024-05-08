<?php

namespace App\Controller;

use App\Entity\CollectionContainer;
use App\Entity\CollectionCustomField;
use App\Form\CustomFieldType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/field')]
class CollectionCustomFieldController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/new/{containerId}', name: 'collection_custom_field_new')]
    public function new(Request $request, int $containerId): Response
    {
        $container = $this->entityManager->getRepository(CollectionContainer::class)->find($containerId);
        if (!$container) {
            throw $this->createNotFoundException('Container not found.');
        }

        $this->denyAccessUnlessGranted('field', $container);

        $customField = new CollectionCustomField();
        $customField->setCollectionContainer($container);

        $form = $this->createForm(CustomFieldType::class, $customField);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($customField);
            $this->entityManager->flush();

            return $this->redirectToRoute('collection_container_show', ['id' => $containerId]);
        }

        return $this->render('collection_custom_field/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/edit', name: 'collection_custom_field_edit')]
    public function edit(Request $request, CollectionCustomField $field): Response
    {
        $container = $field->getCollectionContainer();
        $this->denyAccessUnlessGranted('field', $container);

        $form = $this->createForm(CustomFieldType::class, $field);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();
            return $this->redirectToRoute('collection_container_show', ['id' => $container->getId()]);
        }

        return $this->render('collection_custom_field/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/delete', name: 'collection_custom_field_delete', methods: ['POST'])]
    public function delete(Request $request, CollectionCustomField $field): Response
    {
        $container = $field->getCollectionContainer();
        $this->denyAccessUnlessGranted('field', $container);

        foreach ($field->getCustomFieldValues() as $customFieldValue) {
            $this->entityManager->remove($customFieldValue);
        }

        $container->removeCustomField($field);
        $this->entityManager->remove($field);
        $this->entityManager->flush();

        return $this->redirectToRoute('collection_container_show', ['id' => $container->getId()]);
    }
}
