<?php

namespace App\Service;

use Elastica\Query;
use FOS\ElasticaBundle\Finder\FinderInterface;

class SearchService
{
    private FinderInterface $tagFinder;
    private FinderInterface $commentFinder;
    private FinderInterface $itemFinder;
    private FinderInterface $containerFinder;

    public function __construct(
        FinderInterface $tagFinder,
        FinderInterface $commentFinder,
        FinderInterface $itemFinder,
        FinderInterface $containerFinder
    ) {
        $this->tagFinder = $tagFinder;
        $this->commentFinder = $commentFinder;
        $this->itemFinder = $itemFinder;
        $this->containerFinder = $containerFinder;
    }

    public function searchTags(string $query)
    {
        return $this->tagFinder->find($query);
    }

    public function searchComments(string $query)
    {
        return $this->commentFinder->find($query);
    }

    public function searchItems(string $query)
    {
        return $this->itemFinder->find($query);
    }

    public function searchContainers(string $query)
    {
        return $this->containerFinder->find($query);
    }
}
