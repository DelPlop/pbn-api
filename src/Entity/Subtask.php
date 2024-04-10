<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Repository\SubtaskRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: SubtaskRepository::class)]
#[ORM\Table(name: '`subtask`')]
#[ApiResource(
    operations: [
        new Patch(
            security: 'is_granted("ROLE_USER")',
        ),
        new Delete(
            security: 'is_granted("ROLE_USER")',
        ),
    ],
    normalizationContext: ['groups' => ['subtask:read']],
    denormalizationContext: ['groups' => ['subtask:write']],
    paginationEnabled: false,
)]
class Subtask
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'subtasks')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['subtask:write'])]
    private ?Note $parentNote = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Groups(['subtask:read', 'subtask:write', 'note:read', 'note:write'])]
    private ?string $label = null;

    #[ORM\Column]
    #[Groups(['subtask:read', 'subtask:write', 'note:write'])]
    private ?bool $done = null;

    #[ORM\Column(name: 'position', type: 'integer')]
    #[Gedmo\SortablePosition]
    #[Groups(['subtask:read', 'subtask:write', 'note:read', 'note:write'])]
    private ?int $position = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $owner = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getParentNote(): ?Note
    {
        return $this->parentNote;
    }

    public function setParentNote(?Note $parentNote): static
    {
        $this->parentNote = $parentNote;

        return $this;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(string $label): static
    {
        $this->label = $label;

        return $this;
    }

    public function isDone(): ?bool
    {
        return $this->done;
    }

    public function setDone(bool $done): static
    {
        $this->done = $done;

        return $this;
    }

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setPosition(int $position): static
    {
        $this->position = $position;

        return $this;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): static
    {
        $this->owner = $owner;

        return $this;
    }
}
