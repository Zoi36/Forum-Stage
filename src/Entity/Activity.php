<?php

namespace App\Entity;

use App\Repository\ActivityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ActivityRepository::class)
 */
class Activity
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $nameActivity;

    /**
     * @ORM\Column(type="text")
     */
    private $descriptionActivity;

    /**
     * @ORM\OneToMany(targetEntity=Conversation::class, mappedBy="activity")
     */
    private $conversations;

    /**
     * @ORM\OneToMany(targetEntity=Comment::class, mappedBy="activity")
     */
    private $comments;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $imageActivity;

    public function __construct()
    {
        $this->conversations = new ArrayCollection();
        $this->comments = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNameActivity(): ?string
    {
        return $this->nameActivity;
    }

    public function setNameActivity(string $nameActivity): self
    {
        $this->nameActivity = $nameActivity;

        return $this;
    }

    public function getDescriptionActivity(): ?string
    {
        return $this->descriptionActivity;
    }

    public function setDescriptionActivity(string $descriptionActivity): self
    {
        $this->descriptionActivity = $descriptionActivity;

        return $this;
    }

    /**
     * @return Collection|Conversation[]
     */
    public function getConversations(): Collection
    {
        return $this->conversations;
    }

    public function addConversation(Conversation $conversation): self
    {
        if (!$this->conversations->contains($conversation)) {
            $this->conversations[] = $conversation;
            $conversation->setActivity($this);
        }

        return $this;
    }

    public function removeConversation(Conversation $conversation): self
    {
        if ($this->conversations->contains($conversation)) {
            $this->conversations->removeElement($conversation);
            // set the owning side to null (unless already changed)
            if ($conversation->getActivity() === $this) {
                $conversation->setActivity(null);
            }
        }

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
            $comment->setActivity($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): self
    {
        if ($this->comments->contains($comment)) {
            $this->comments->removeElement($comment);
            // set the owning side to null (unless already changed)
            if ($comment->getActivity() === $this) {
                $comment->setActivity(null);
            }
        }

        return $this;
    }

    public function getImageActivity(): ?string
    {
        return $this->imageActivity;
    }

    public function setImageActivity(?string $imageActivity): self
    {
        $this->imageActivity = $imageActivity;

        return $this;
    }


}
