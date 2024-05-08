<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity]
class CollectionCustomField
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    private ?string $type = null;

    #[ORM\ManyToOne(targetEntity: CollectionContainer::class, inversedBy: 'customFields')]
    #[ORM\JoinColumn(name: 'container_id', referencedColumnName: 'id')]
    private ?CollectionContainer $container = null;

    #[ORM\OneToMany(
        targetEntity: CollectionCustomFieldValue::class,
        mappedBy: 'customField',
        cascade: ['persist', 'remove']
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

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;
        return $this;
    }

    public function getCollectionContainer(): ?CollectionContainer
    {
        return $this->container;
    }

    public function setCollectionContainer(?CollectionContainer $container): self
    {
        $this->container = $container;
        return $this;
    }

    public function getCustomFieldValues(): Collection
    {
        return $this->customFieldValues;
    }
}
