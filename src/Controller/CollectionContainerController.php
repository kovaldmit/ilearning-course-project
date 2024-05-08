<?php

namespace App\Controller;

use App\Entity\CollectionContainer;
use App\Entity\Comment;
use App\Entity\Like;
use App\Entity\Tag;
use App\Form\CollectionContainerType;
use App\Form\CommentType;
use App\Form\TagType;
use App\Repository\CollectionContainerRepository;
use App\Repository\TagRepository;
use App\Service\FileUploader;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/collection')]
class CollectionContainerController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private FileUploader $fileUploader;

    public function __construct(EntityManagerInterface $entityManager, FileUploader $fileUploader)
    {
        $this->entityManager = $entityManager;
        $this->fileUploader = $fileUploader;
    }

    #[Route('/', name: 'collection_container_index', methods: ['GET'])]
    public function index(CollectionContainerRepository $containerRepository): Response
    {
        $containers = $containerRepository->findAll();
        return $this->render('collection_container/index.html.twig', [
            'containers' => $containers,
        ]);
    }

    #[Route('/new', name: 'collection_container_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $container = new CollectionContainer();
        $this->denyAccessUnlessGranted('new', $container);

        $form = $this->createForm(CollectionContainerType::class, $container);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form->get('image')->getData();
            if ($file) {
                $fileName = uniqid() . '.' . $file->guessExtension();
                $this->fileUploader->uploadFile($file, $fileName);
                $container->setImage($fileName);
            }

            $container->setUser($this->getUser());
            $this->entityManager->persist($container);
            $this->entityManager->flush();

            return $this->redirectToRoute('collection_container_index');
        }

        return $this->render('collection_container/new.html.twig', [
            'container' => $container,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/search_tags', name: 'collection_container_search_tags', methods: ['GET'])]
    public function searchTags(Request $request, TagRepository $tagRepository): JsonResponse
    {
        $query = $request->query->get('q', '');
        $tags = $tagRepository->findByPartialName($query);

        return $this->json($tags);
    }

    /**
     * @throws Exception
     */
    #[Route('/{id}', name: 'collection_container_show', methods: ['GET', 'POST'])]
    public function show(Request $request, CollectionContainer $container, TagRepository $tagRepository): Response
    {
        $containerImageUrl = $container->getImage() ? $this->fileUploader->getFileUrl($container->getImage()) : '';

        // Tag form
        $newTag = new Tag();
        $tagForm = $this->createForm(TagType::class, $newTag);
        $tagForm->handleRequest($request);

        if ($tagForm->isSubmitted() && $tagForm->isValid()) {
            $this->entityManager->persist($newTag);
            $this->entityManager->flush();
            $container->addTag($newTag);
            $this->entityManager->persist($container);
            $this->entityManager->flush();

            return $this->redirectToRoute('collection_container_show', ['id' => $container->getId()]);
        }

        $tags = $tagRepository->findAll();

        // Like status
        $user = $this->getUser();
        $isLiked = $this->entityManager->getRepository(Like::class)->findOneBy([
                'user' => $user,
                'container' => $container,
            ]) !== null;

        // Comment form
        $comment = new Comment();
        $commentForm = $this->createForm(CommentType::class, $comment);
        $commentForm->handleRequest($request);

        if ($commentForm->isSubmitted() && $commentForm->isValid()) {
            if (!$this->isGranted('ROLE_ADMIN') && !$this->isGranted('ROLE_USER')) {
                throw $this->createAccessDeniedException('You do not have permission to add comments.');
            }

            $comment->setUser($this->getUser());
            $comment->setCollectionContainer($container);
            $this->entityManager->persist($comment);
            $this->entityManager->flush();

            return $this->redirectToRoute('collection_container_show', ['id' => $container->getId()]);
        }

        $comments = $this->entityManager->getRepository(Comment::class)->findBy(['container' => $container]);

        return $this->render('collection_container/show.html.twig', [
            'form' => $tagForm->createView(),
            'container' => $container,
            'containerImageUrl' => $containerImageUrl,
            'tags' => $tags,
            'isLiked' => $isLiked,
            'comments' => $comments,
            'commentForm' => $commentForm->createView(),
        ]);
    }

    #[Route('/{id}/edit', name: 'collection_container_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, CollectionContainer $container): Response
    {
        $this->denyAccessUnlessGranted('edit', $container);

        $form = $this->createForm(CollectionContainerType::class, $container);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form->get('image')->getData();
            if ($file) {
                $fileName = uniqid() . '.' . $file->guessExtension();
                $this->fileUploader->uploadFile($file, $fileName);
                $container->setImage($fileName);
            }

            $this->entityManager->flush();

            return $this->redirectToRoute('collection_container_show', ['id' => $container->getId()]);
        }

        return $this->render('collection_container/edit.html.twig', [
            'container' => $container,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/delete', name: 'collection_container_delete', methods: ['POST'])]
    public function delete(Request $request, CollectionContainer $container): Response
    {
        $this->denyAccessUnlessGranted('delete', $container);

        if ($this->isCsrfTokenValid('delete' . $container->getId(), $request->request->get('_token'))) {
            $this->entityManager->remove($container);
            $this->entityManager->flush();
        }

        return $this->redirectToRoute('collection_container_index');
    }

    #[Route('/{id}/tags/add', name: 'collection_container_add_tag', methods: ['POST'])]
    public function addTagToContainer(
        Request $request,
        CollectionContainer $container,
        TagRepository $tagRepository
    ): JsonResponse {
        $this->denyAccessUnlessGranted('edit', $container);

        $data = json_decode($request->getContent(), true);
        $tagId = $data['tagId'] ?? null;
        $tagName = $data['tagName'] ?? null;

        if ($tagId) {
            $tag = $tagRepository->find($tagId);
        } elseif ($tagName) {
            $tagName = ucfirst(strtolower($tagName));
            $tag = $tagRepository->findOneBy(['name' => $tagName]);

            if (!$tag) {
                $tag = new Tag();
                $tag->setName($tagName);
                $this->entityManager->persist($tag);
                $this->entityManager->flush();
            }
        } else {
            return $this->json(['success' => false, 'message' => 'Invalid tag data'], Response::HTTP_BAD_REQUEST);
        }

        if ($tag) {
            $container->addTag($tag);
            $this->entityManager->flush();
        }

        return $this->json(['success' => true, 'tag' => ['id' => $tag->getId(), 'name' => $tag->getName()]]);
    }

    #[Route('/{id}/tags/remove', name: 'collection_container_remove_tag', methods: ['POST'])]
    public function removeTagFromContainer(
        Request $request,
        CollectionContainer $container,
        TagRepository $tagRepository
    ): JsonResponse {
        $this->denyAccessUnlessGranted('edit', $container);

        $data = json_decode($request->getContent(), true);
        $tagId = $data['tagId'] ?? null;

        if (!$tagId) {
            return $this->json(['success' => false, 'message' => 'Tag ID is missing'], Response::HTTP_BAD_REQUEST);
        }

        $tag = $tagRepository->find($tagId);
        if (!$tag) {
            return $this->json(['success' => false, 'message' => 'Tag not found'], Response::HTTP_NOT_FOUND);
        }

        $container->removeTag($tag);
        $this->entityManager->flush();

        return $this->json(['success' => true]);
    }

    #[Route('/{id}/like', name: 'collection_container_like', methods: ['POST'])]
    public function like(Request $request, CollectionContainer $container): JsonResponse
    {
        $user = $this->getUser();

        $like = true;

        if ($user) {
            $likeRepository = $this->entityManager->getRepository(Like::class);

            $like = $likeRepository->findOneBy([
                'user' => $user,
                'container' => $container,
            ]);

            if ($like) {
                $container->decrementLikesCount();
                $this->entityManager->remove($like);
            } else {
                $newLike = new Like();
                $newLike->setUser($user);
                $newLike->setCollectionContainer($container);
                $container->incrementLikesCount();
                $this->entityManager->persist($newLike);
            }

            $this->entityManager->flush();
        }

        return new JsonResponse([
            'likesCount' => $container->getLikesCount(),
            'isLiked' => !$like,
        ]);
    }
}
