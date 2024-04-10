<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Serializer\Filter\PropertyFilter;
use App\Repository\CategoryRepository;
use App\State\CategoryOrNoteSetOwnerProcessor;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CategoryRepository::class)]
#[ORM\Table(name: '`category`')]
#[ApiResource(
    operations: [
        new Get(
            normalizationContext: [
                'groups' => ['category:read', 'category:item:get'],
            ],
            security: 'is_granted("ROLE_USER") and object.getOwner() == user',
        ),
        new GetCollection(
            normalizationContext: [
                'groups' => ['category:read', 'category:item:collection'],
            ],
            security: 'is_granted("ROLE_USER") and object.getOwner() == user',
        ),
        new Post(
            security: 'is_granted("ROLE_USER")',
            processor: CategoryOrNoteSetOwnerProcessor::class,
        ),
        new Patch(
            security: 'is_granted("ROLE_USER") and object.getOwner() == user',
            securityPostDenormalize: 'object.getOwner() == user',
        ),
        new Delete(
            security: 'is_granted("ROLE_USER") and object.getOwner() == user',
        ),
    ],
    normalizationContext: ['groups' => ['category:read']],
    denormalizationContext: ['groups' => ['category:write']],
    order: ['position' => 'ASC'],
    paginationEnabled: false,
)]
#[ApiFilter(PropertyFilter::class)]
class Category
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Groups(['category:read', 'category:write', 'note:read'])]
    private ?string $name = null;

    #[ORM\Column(name: 'position', type: 'integer')]
    #[Groups(['category:read', 'category:write', 'note:read'])]
    #[Gedmo\SortablePosition]
    private ?int $position = null;

    #[ORM\ManyToMany(targetEntity: Note::class, mappedBy: 'categories')]
    #[Groups(['category:item:get'])]
    private Collection $notes;

    #[ORM\ManyToOne(inversedBy: 'categories')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['category:write'])]
    #[ApiFilter(SearchFilter::class, strategy: 'exact')]
    private ?User $owner = null;

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
            $note->addCategory($this);
        }

        return $this;
    }

    public function removeNote(Note $note): static
    {
        if ($this->notes->removeElement($note)) {
            $note->removeCategory($this);
        }

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
