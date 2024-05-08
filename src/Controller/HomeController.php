<?php

namespace App\Controller;

use App\Repository\CollectionContainerRepository;
use App\Service\FileUploader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private FileUploader $fileUploader;

    public function __construct(EntityManagerInterface $entityManager, FileUploader $fileUploader)
    {
        $this->entityManager = $entityManager;
        $this->fileUploader = $fileUploader;
    }

    #[Route(path: '/', name: 'home')]
    public function index(CollectionContainerRepository $containerRepository): Response
    {
        $latestContainers = $containerRepository->findBy([], ['createdAt' => 'DESC'], 4);

        $queryBuilder = $this->entityManager->createQueryBuilder();
        $queryBuilder->select('a')
            ->from('App\Entity\CollectionContainer', 'a')
            ->leftJoin('a.items', 'b')
            ->groupBy('a.id')
            ->orderBy('COUNT(b.id)', 'DESC')
            ->setMaxResults(4);
        $biggestContainers = $queryBuilder->getQuery()->getResult();

        return $this->render('pages/home.html.twig', [
            'latestContainers' => $latestContainers,
            'biggestContainers' => $biggestContainers,
            'fileUploader' => $this->fileUploader,
        ]);
    }
}
