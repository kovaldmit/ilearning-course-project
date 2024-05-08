<?php

namespace App\Controller;

use App\Entity\Comment;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/comment')]
class CommentController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/{id}/delete', name: 'comment_delete', methods: ['POST'])]
    public function deleteComment(Request $request, Comment $comment): Response
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException('You do not have permission to delete comments.');
        }

        if ($this->isCsrfTokenValid('delete' . $comment->getId(), $request->request->get('_token'))) {
            $this->entityManager->remove($comment);
            $this->entityManager->flush();
        }

        return $this->redirectToRoute('collection_container_show', ['id' => $comment->getCollectionContainer()->getId()]);
    }
}
