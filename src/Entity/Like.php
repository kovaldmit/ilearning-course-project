<?php

namespace App\Entity;

use App\Repository\LikeRepository;
use DateTime;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LikeRepository::class)]
#[ORM\Table(name: '`like`')]
class Like
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'likes')]
    #[ORM\JoinColumn(nullable: false)]
    private User $user;

    #[ORM\ManyToOne(targetEntity: CollectionContainer::class, inversedBy: 'likes')]
    #[ORM\JoinColumn(name: 'container_id', referencedColumnName: 'id', nullable: false)]
    private CollectionContainer $container;

    #[ORM\Column(type: 'datetime')]
    private DateTimeInterface $likedAt;

    public function __construct()
    {
        $this->likedAt = new DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;
        return $this;
    }

    public function getCollectionContainer(): CollectionContainer
    {
        return $this->container;
    }

    public function setCollectionContainer(CollectionContainer $container): self
    {
        $this->container = $container;
        return $this;
    }

    public function getLikedAt(): DateTimeInterface
    {
        return $this->likedAt;
    }

    public function setLikedAt(DateTimeInterface $likedAt): self
    {
        $this->likedAt = $likedAt;
        return $this;
    }
}
