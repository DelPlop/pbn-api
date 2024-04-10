<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Serializer\Filter\PropertyFilter;
use App\Repository\NoteRepository;
use App\State\CategoryOrNoteSetOwnerProcessor;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: NoteRepository::class)]
#[ORM\Table(name: '`note`')]
#[ApiResource(
    operations: [
        new GetCollection(
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
    normalizationContext: ['groups' => ['note:read']],
    denormalizationContext: ['groups' => ['note:write']],
    paginationEnabled: false,
)]
#[ApiFilter(PropertyFilter::class)]
class Note
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Groups(['note:read', 'note:write', 'category:item:get'])]
    #[ApiFilter(SearchFilter::class, strategy: 'partial')]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(['note:read', 'note:write', 'category:item:get'])]
    #[ApiFilter(SearchFilter::class, strategy: 'partial')]
    private ?string $content = null;

    #[ORM\Column(length: 20)]
    #[Groups(['note:read', 'note:write', 'category:item:get'])]
    #[ApiFilter(SearchFilter::class, strategy: 'exact')]
    private ?string $visibility = null;

    #[ORM\Column(length: 5)]
    #[Groups(['note:read', 'note:write', 'category:item:get'])]
    #[ApiFilter(SearchFilter::class, strategy: 'exact')]
    private ?string $type = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    #[Groups(['note:read', 'note:write', 'category:item:get'])]
    #[ApiFilter(DateFilter::class)]
    private ?\DateTimeInterface $dueDate = null;

    #[ORM\ManyToOne(inversedBy: 'notes')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['note:read', 'note:write', 'category:item:get'])]
    #[ApiFilter(SearchFilter::class, strategy: 'exact')]
    #[Assert\NotNull]
    private ?Priority $priority = null;

    #[ORM\ManyToOne(inversedBy: 'notes')]
    #[Groups(['note:read', 'note:write', 'category:item:get'])]
    #[ApiFilter(SearchFilter::class, strategy: 'exact')]
    private ?State $state = null;

    #[ORM\ManyToMany(targetEntity: Category::class, inversedBy: 'notes')]
    #[Groups(['note:read', 'note:write', 'category:item:get'])]
    #[ApiFilter(SearchFilter::class, strategy: 'exact')]
    #[Assert\NotNull]
    private Collection $categories;

    #[ORM\OneToMany(targetEntity: Subtask::class, mappedBy: 'parentNote', cascade: ['persist'], orphanRemoval: true)]
    #[Groups(['note:read', 'note:write', 'category:item:get'])]
    private Collection $subtasks;

    #[ORM\ManyToOne(inversedBy: 'notes')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['note:write'])]
    #[ApiFilter(SearchFilter::class, strategy: 'exact')]
    private ?User $owner = null;

    public function __construct()
    {
        $this->categories = new ArrayCollection();
        $this->subtasks = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): static
    {
        $this->content = $content;

        return $this;
    }

    public function getVisibility(): ?string
    {
        return $this->visibility;
    }

    public function setVisibility(string $visibility): static
    {
        $this->visibility = $visibility;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getDueDate(): ?\DateTimeInterface
    {
        return $this->dueDate;
    }

    public function setDueDate(?\DateTimeInterface $dueDate): static
    {
        $this->dueDate = $dueDate;

        return $this;
    }

    public function getPriority(): ?Priority
    {
        return $this->priority;
    }

    public function setPriority(?Priority $priority): static
    {
        $this->priority = $priority;

        return $this;
    }

    public function getState(): ?State
    {
        return $this->state;
    }

    public function setState(?State $state): static
    {
        $this->state = $state;

        return $this;
    }

    /**
     * @return Collection<int, Category>
     */
    public function getCategories(): Collection
    {
        return $this->categories;
    }

    public function addCategory(Category $category): static
    {
        if (!$this->categories->contains($category)) {
            $this->categories->add($category);
        }

        return $this;
    }

    public function removeCategory(Category $category): static
    {
        $this->categories->removeElement($category);

        return $this;
    }

    /**
     * @return Collection<int, Subtask>
     */
    public function getSubtasks(): Collection
    {
        return $this->subtasks;
    }

    public function addSubtask(Subtask $subtask): static
    {
        if (!$this->subtasks->contains($subtask)) {
            $this->subtasks->add($subtask);
            $subtask->setParentNote($this);
            $subtask->setOwner($this->getOwner());
        }

        return $this;
    }

    public function removeSubtask(Subtask $subtask): static
    {
        if ($this->subtasks->removeElement($subtask)) {
            // set the owning side to null (unless already changed)
            if ($subtask->getParentNote() === $this) {
                $subtask->setParentNote(null);
            }
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
