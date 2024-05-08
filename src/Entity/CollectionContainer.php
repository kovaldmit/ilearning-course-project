<?php

namespace App\Entity;

use App\Repository\CollectionContainerRepository;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Parsedown;

#[ORM\Entity(repositoryClass: CollectionContainerRepository::class)]
class CollectionContainer
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $image = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: "user_id", referencedColumnName: "id")]
    private ?User $user = null;

    #[ORM\ManyToOne(targetEntity: CollectionCategory::class)]
    #[ORM\JoinColumn(name: "category_id", referencedColumnName: "id")]
    private ?CollectionCategory $category = null;

    #[ORM\Column(type: 'datetime')]
    private DateTime $createdAt;

    #[ORM\OneToMany(targetEntity: CollectionItem::class, mappedBy: 'container', orphanRemoval: true)]
    private Collection $items;

    #[ORM\OneToMany(targetEntity: CollectionCustomField::class, mappedBy: 'container', orphanRemoval: true)]
    private Collection $customFields;

    #[ORM\ManyToMany(targetEntity: Tag::class, inversedBy: 'containers', orphanRemoval: true)]
    #[ORM\JoinTable(name: 'collection_container_tags')]
    private Collection $tags;

    #[ORM\OneToMany(targetEntity: Like::class, mappedBy: 'container', orphanRemoval: true)]
    private Collection $likes;

    #[ORM\Column(type: 'integer')]
    private int $likesCount = 0;

    #[ORM\OneToMany(targetEntity: Comment::class, mappedBy: 'container', orphanRemoval: true)]
    private Collection $comments;

    public function __construct()
    {
        $this->createdAt = new DateTime();
        $this->items = new ArrayCollection();
        $this->customFields = new ArrayCollection();
        $this->tags = new ArrayCollection();
        $this->likes = new ArrayCollection();
        $this->comments = new ArrayCollection();
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getFormattedDescription(): ?string
    {
        $parsedown = new Parsedown();

        return $parsedown->text($this->description);
    }

    public function getUnformattedDescription(): ?string
    {
        $parsedown = new Parsedown();
        $descriptionHtml = $parsedown->text($this->description);

        return strip_tags($descriptionHtml);
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): self
    {
        $this->image = $image;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
    }

    public function getCollectionCategory(): ?CollectionCategory
    {
        return $this->category;
    }

    public function setCollectionCategory(?CollectionCategory $category): self
    {
        $this->category = $category;

        return $this;
    }

    /**
     * @return Collection
     */
    public function getItems(): Collection
    {
        return $this->items;
    }

    public function addItem(CollectionItem $item): self
    {
        if (!$this->items->contains($item)) {
            $this->items[] = $item;
            $item->setCollectionContainer($this);
        }

        return $this;
    }

    public function removeItem(CollectionItem $item): self
    {
        if ($this->items->removeElement($item)) {
            if ($item->getCollectionContainer() === $this) {
                $item->setCollectionContainer(null);
            }
        }

        return $this;
    }

    public function getCustomFields(): Collection
    {
        return $this->customFields;
    }

    public function addCustomField(CollectionCustomField $customField): self
    {
        if (!$this->customFields->contains($customField)) {
            $this->customFields[] = $customField;
            $customField->setCollectionContainer($this);
        }

        return $this;
    }

    public function removeCustomField(CollectionCustomField $customField): self
    {
        if ($this->customFields->removeElement($customField)) {
            if ($customField->getCollectionContainer() === $this) {
                $customField->setCollectionContainer(null);
            }
        }

        return $this;
    }

    public function getTags(): Collection
    {
        return $this->tags;
    }

    public function addTag(Tag $tag): self
    {
        if (!$this->tags->contains($tag)) {
            $this->tags->add($tag);
            $tag->addContainer($this);
        }
        return $this;
    }

    public function removeTag(Tag $tag): self
    {
        if ($this->tags->removeElement($tag)) {
            $tag->removeContainer($this);
        }
        return $this;
    }

    public function getLikes(): Collection
    {
        return $this->likes;
    }

    public function addLike(Like $like): self
    {
        if (!$this->likes->contains($like)) {
            $this->likes->add($like);
            $like->setCollectionContainer($this);
        }

        return $this;
    }

    public function removeLike(Like $like): self
    {
        if ($this->likes->removeElement($like)) {
            if ($like->getCollectionContainer() === $this) {
                $like->setCollectionContainer(null);
            }
        }

        return $this;
    }

    public function getLikesCount(): int
    {
        return $this->likesCount;
    }

    public function incrementLikesCount(): self
    {
        $this->likesCount++;
        return $this;
    }

    public function decrementLikesCount(): self
    {
        $this->likesCount--;
        return $this;
    }

    /**
     * @return Collection|Comment[]
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comment $comment): self
    {
        if (!$this->comments->contains($comment)) {
            $this->comments[] = $comment;
            $comment->setCollectionContainer($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): self
    {
        if ($this->comments->removeElement($comment)) {
            if ($comment->getCollectionContainer() === $this) {
                $comment->setCollectionContainer(null);
            }
        }

        return $this;
    }
}
