<?php

namespace App\Entity;

use App\Repository\TagRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TagRepository::class)]
class Tag
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    private string $name;

    #[ORM\ManyToMany(targetEntity: CollectionContainer::class, mappedBy: 'tags')]
    private Collection $containers;

    public function __construct()
    {
        $this->containers = new ArrayCollection();
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

    public function getContainers(): Collection
    {
        return $this->containers;
    }

    public function addContainer(CollectionContainer $container): self
    {
        if (!$this->containers->contains($container)) {
            $this->containers->add($container);
            $container->addTag($this);
        }
        return $this;
    }

    public function removeContainer(CollectionContainer $container): self
    {
        if ($this->containers->removeElement($container)) {
            $container->removeTag($this);
        }
        return $this;
    }
}
