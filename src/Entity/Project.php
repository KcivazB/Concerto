<?php

namespace App\Entity;

use App\Entity\Tag;
use App\Entity\Idea;
use App\Entity\User;
use App\Entity\Invitation;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\ProjectRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity(repositoryClass=ProjectRepository::class)
 */
class Project
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $title;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @ORM\OneToMany(targetEntity=Idea::class, mappedBy="project")
     */
    private $ideas;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="projects")
     * @ORM\JoinColumn(nullable=false)
     */
    private $owner;

    /**
     * @ORM\ManyToMany(targetEntity=User::class, inversedBy="projects")
     */
    private $contributors;

    /**
     * @ORM\Column(type="datetime")
     */
    private $startDate;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $isDelete;

    /**
     * @ORM\OneToMany(targetEntity=Invitation::class, mappedBy="project")
     */
    private $invitations;

    /**
     * @ORM\ManyToMany(targetEntity=Tag::class, inversedBy="projects")
     */
    private $tags;

    public function __construct()
    {
        $this->ideas = new ArrayCollection();
        $this->contributors = new ArrayCollection();
        $this->invitations = new ArrayCollection();
        $this->tags = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

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

    /**
     * @return Collection<int, Idea>
     */
    public function getIdeas(): Collection
    {
        $ideas = new ArrayCollection();
        
        foreach ($this->ideas as $idea) {
            if ($idea->isIsDelete() === false || $idea->isIsDelete() === null) {
                $ideas->add($idea);
            }
        }

        return $ideas;
    }

    public function addIdea(Idea $idea): self
    {
        if (!$this->ideas->contains($idea)) {
            $this->ideas[] = $idea;
            $idea->setProject($this);
        }

        return $this;
    }

    public function removeIdea(Idea $idea): self
    {
        if ($this->ideas->removeElement($idea)) {
            // set the owning side to null (unless already changed)
            if ($idea->getProject() === $this) {
                $idea->setProject(null);
            }
        }

        return $this;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): self
    {
        $this->owner = $owner;

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getContributors(): Collection
    {
        return $this->contributors;
    }

    public function addContributor(User $contributor): self
    {
        if (!$this->contributors->contains($contributor)) {
            $this->contributors[] = $contributor;
        }

        return $this;
    }

    public function removeContributor(User $contributor): self
    {
        $this->contributors->removeElement($contributor);

        return $this;
    }

    public function getStartDate(): ?\DateTimeInterface
    {
        return $this->startDate;
    }

    public function setStartDate(\DateTimeInterface $startDate): self
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function isIsDelete(): ?bool
    {
        return $this->isDelete;
    }

    public function setIsDelete(?bool $isDelete): self
    {
        $this->isDelete = $isDelete;

        return $this;
    }

    /**
     * @return Collection<int, Invitation>
     */
    public function getInvitations(): Collection
    {
        return $this->invitations;
    }

    public function addInvitation(Invitation $invitation): self
    {
        if (!$this->invitations->contains($invitation)) {
            $this->invitations[] = $invitation;
            $invitation->setProject($this);
        }

        return $this;
    }

    public function removeInvitation(Invitation $invitation): self
    {
        if ($this->invitations->removeElement($invitation)) {
            // set the owning side to null (unless already changed)
            if ($invitation->getProject() === $this) {
                $invitation->setProject(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Tag>
     */
    public function getTags(): Collection
    {
        return $this->tags;
    }

    public function addTag(Tag $tag): self
    {
        if (!$this->tags->contains($tag)) {
            $this->tags[] = $tag;
        }

        return $this;
    }

    public function removeTag(Tag $tag): self
    {
        $this->tags->removeElement($tag);

        return $this;
    }
    
}
