<?php

namespace App\Entity;

use App\Repository\NotificationRepository;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Persistence\ManagerRegistry;

#[ORM\HasLifecycleCallbacks()]
#[ORM\Entity(repositoryClass: NotificationRepository::class)]
class Notification
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $action = null;

    #[ORM\ManyToOne(inversedBy: 'notification')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'notification')]
    private ?Folder $folder = null;

    #[ORM\ManyToOne(inversedBy: 'Notification')]
    private ?Document $document = null;

    #[ORM\ManyToOne(inversedBy: 'notification')]
    private ?Comment $comment = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\ManyToOne(inversedBy: 'notification')]
    private ?Reminder $reminder = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAction(): ?string
    {
        return $this->action;
    }

    public function setAction(string $action): self
    {
        $this->action = $action;

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

    public function getFolder(): ?Folder
    {
        return $this->folder;
    }

    public function setFolder(?Folder $folder): self
    {
        $this->folder = $folder;

        return $this;
    }

    public function getDocument(): ?Document
    {
        return $this->document;
    }

    public function setDocument(?Document $document): self
    {
        $this->document = $document;

        return $this;
    }

    public function getComment(): ?Comment
    {
        return $this->comment;
    }

    public function setComment(?Comment $comment): self
    {
        $this->comment = $comment;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }


    #[ORM\PrePersist()]
     
    public function onPrePersist() {
        $this->createdAt = new \DateTimeImmutable();
    }

    #[ORM\PreUpdate()]
    public function onPreUpdate() {
    }

    #[ORM\PostPersist()]

    public function deleteIfOlderThanOneMonth( )
    {
          /** @var ManagerRegistry $doctrine */
        

        $oneMonthAgo = new DateTime('-1 month');

        if ($this->createdAt < $oneMonthAgo) {
            $entityManager = $doctrine->getManager();
            $entityManager->remove($this);
            $entityManager->flush();
        }
    }


    public function getReminder(): ?Reminder
    {
        return $this->reminder;
    }

    public function setReminder(?Reminder $reminder): self
    {
        $this->reminder = $reminder;

        return $this;
    }
}
