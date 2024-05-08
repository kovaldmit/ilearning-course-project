<?php

namespace App\Entity;

use App\Repository\CollectionItemRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CollectionItemRepository::class)]
class CollectionItem
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\ManyToOne(targetEntity: CollectionContainer::class, inversedBy: 'items')]
    #[ORM\JoinColumn(name: 'container_id', referencedColumnName: 'id')]
    private ?CollectionContainer $container = null;

    #[ORM\OneToMany(
        targetEntity: CollectionCustomFieldValue::class,
        mappedBy: 'item',
        cascade: ['persist', 'remove'],
        orphanRemoval: true
    )]
    private Collection $customFieldValues;

    public function __construct()
    {
        $this->customFieldValues = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;
        return $this;
    }

    public function getCollectionContainer(): ?CollectionContainer
    {
        return $this->container;
    }

    public function setCollectionContainer(?CollectionContainer $container): static
    {
        $this->container = $container;
        return $this;
    }

    public function getCustomFieldValues(): Collection
    {
        return $this->customFieldValues;
    }

    public function getCustomFieldValue(CollectionCustomField $customField): ?CollectionCustomFieldValue
    {
        foreach ($this->customFieldValues as $customFieldValue) {
            if ($customFieldValue->getCustomField() === $customField) {
                return $customFieldValue;
            }
        }

        return null;
    }
}
