<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Repository\PriorityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: PriorityRepository::class)]
#[ORM\Table(name: '`priority`')]
#[ApiResource(
    description: 'Note priority',
    operations: [
        new Get(
            normalizationContext: [
                'groups' => ['priority:read', 'priority:item:get'],
            ],
            security: 'is_granted("ROLE_ADMIN")',
        ),
        new GetCollection(
            normalizationContext: [
                'groups' => ['priority:read', 'priority:item:collection'],
            ],
        ),
        new Post(
            security: 'is_granted("ROLE_ADMIN")',
        ),
        new Patch(
            security: 'is_granted("ROLE_ADMIN")',
        ),
        new Delete(
            security: 'is_granted("ROLE_ADMIN")',
        ),
    ],
    normalizationContext: ['groups' => ['priority:read']],
    denormalizationContext: ['groups' => ['priority:write']],
    order: ['position' => 'ASC'],
    paginationEnabled: false,
)]
class Priority
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank]
    #[Groups(['priority:read', 'priority:write', 'note:read'])]
    private ?string $name = null;

    #[ORM\Column(name: 'position', type: 'integer')]
    #[Gedmo\SortablePosition]
    #[Groups(['priority:read', 'priority:write'])]
    private ?int $position = null;

    #[ORM\OneToMany(targetEntity: Note::class, mappedBy: 'priority')]
    #[Groups(['priority:item:get'])]
    private Collection $notes;

    public function __construct()
    {
        $this->notes = new ArrayCollection();
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

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setPosition(int $position): static
    {
        $this->position = $position;

        return $this;
    }

    /**
     * @return Collection<int, Note>
     */
    public function getNotes(): Collection
    {
        return $this->notes;
    }

    public function addNote(Note $note): static
    {
        if (!$this->notes->contains($note)) {
            $this->notes->add($note);
            $note->setPriority($this);
        }

        return $this;
    }

    public function removeNote(Note $note): static
    {
        if ($this->notes->removeElement($note)) {
            // set the owning side to null (unless already changed)
            if ($note->getPriority() === $this) {
                $note->setPriority(null);
            }
        }

        return $this;
    }
}
