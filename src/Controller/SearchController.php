<?php

namespace App\Controller;

use App\Form\SearchType;
use FOS\ElasticaBundle\Finder\TransformedFinder;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class SearchController extends AbstractController
{
    private TransformedFinder $containerFinder;
    private TransformedFinder $tagFinder;
    private TransformedFinder $commentFinder;
    private TransformedFinder $itemFinder;

    public function __construct(
        TransformedFinder $containerFinder,
        TransformedFinder $tagFinder,
        TransformedFinder $commentFinder,
        TransformedFinder $itemFinder
    ) {
        $this->containerFinder = $containerFinder;
        $this->tagFinder = $tagFinder;
        $this->commentFinder = $commentFinder;
        $this->itemFinder = $itemFinder;
    }

    #[Route('/search', name: 'search')]
    public function search(Request $request): Response
    {
        $form = $this->createForm(SearchType::class);
        $form->handleRequest($request);

        $results = [];

        if ($form->isSubmitted() && $form->isValid()) {
            $query = $form->get('query')->getData();
            $results['containers'] = $this->containerFinder->find($query);
            $results['tags'] = $this->tagFinder->find($query);
            $results['comments'] = $this->commentFinder->find($query);
            $results['items'] = $this->itemFinder->find($query);
        }

        return $this->render('search/results.html.twig', [
            'form' => $form->createView(),
            'results' => $results,
        ]);
    }
}
