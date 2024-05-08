<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class CollectionCustomFieldValue
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: CollectionCustomField::class, inversedBy: 'customFieldValues')]
    #[ORM\JoinColumn(name: 'custom_field_id', referencedColumnName: 'id')]
    private ?CollectionCustomField $customField = null;

    #[ORM\ManyToOne(targetEntity: CollectionItem::class, inversedBy: 'customFieldValues')]
    #[ORM\JoinColumn(name: 'item_id', referencedColumnName: 'id')]
    private ?CollectionItem $item = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $value = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCustomField(): ?CollectionCustomField
    {
        return $this->customField;
    }

    public function setCustomField(?CollectionCustomField $customField): self
    {
        $this->customField = $customField;
        return $this;
    }

    public function getItem(): ?CollectionItem
    {
        return $this->item;
    }

    public function setItem(?CollectionItem $item): self
    {
        $this->item = $item;
        return $this;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function setValue(?string $value): self
    {
        $this->value = $value;
        return $this;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
