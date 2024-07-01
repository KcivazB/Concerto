<?php

namespace App\Entity;

use App\Repository\TagRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=TagRepository::class)
 */
class Tag
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
     * @ORM\Column(type="string", length=255)
     */
    private $color;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="tags")
     */
    private $owner;

    /**
     * @ORM\ManyToMany(targetEntity=Idea::class, mappedBy="tags")
     */
    private $ideas;

    /**
     * @ORM\ManyToMany(targetEntity=Project::class, mappedBy="tags")
     */
    private $projects;

    /**
     * @ORM\ManyToOne(targetEntity=TagCategory::class, inversedBy="tags")
     */
    private $category;

    public function __construct()
    {
        $this->ideas = new ArrayCollection();
        $this->projects = new ArrayCollection();
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

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(string $color): self
    {
        $this->color = $color;

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
     * @return Collection<int, Idea>
     */
    public function getIdeas(): Collection
    {
        return $this->ideas;
    }

    public function addIdea(Idea $idea): self
    {
        if (!$this->ideas->contains($idea)) {
            $this->ideas[] = $idea;
            $idea->addTag($this);
        }

        return $this;
    }

    public function removeIdea(Idea $idea): self
    {
        if ($this->ideas->removeElement($idea)) {
            $idea->removeTag($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, Idea>
     */
    public function getProjects(): Collection
    {
        return $this->ideas;
    }

    public function addProject(Project $project): self
    {
        if (!$this->projects->contains($project)) {
            $this->projects[] = $project;
            $project->addTag($this);
        }

        return $this;
    }

    public function removeProject(Idea $project): self
    {
        if ($this->projects->removeElement($project)) {
            $project->removeTag($this);
        }

        return $this;
    }

    public function getCategory(): ?TagCategory
    {
        return $this->category;
    }

    public function setCategory(?TagCategory $category): self
    {
        $this->category = $category;

        return $this;
    }
    
}
