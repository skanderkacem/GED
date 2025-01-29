<?php

namespace App\Entity;

use App\Repository\DocumentRepository;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\HasLifecycleCallbacks()]

#[ORM\Entity(repositoryClass: DocumentRepository::class)]
class Document
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $extention = null;

    #[ORM\ManyToOne(inversedBy: 'documents')]
    private ?Folder $folder = null;

    #[ORM\Column(length: 255)]
    private ?string $storageName = null;

    #[ORM\ManyToOne(inversedBy: 'documents')]
    #[ORM\JoinColumn(nullable: true,onDelete:'SET NULL')]
    private ?User $createdBy = null;

    #[ORM\Column]
    private ?bool $IsDeleted = false;

    #[ORM\OneToMany(mappedBy: 'doc', targetEntity: DocVersion::class, cascade:["persist", "remove"],orphanRemoval: true)]
    private Collection $docVersions;

    #[ORM\Column]
    private ?int $currentVersion = 1;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column(type:Types::TEXT, nullable: true)]
    private ?string $textContent = null;

    #[ORM\OneToMany(mappedBy: 'doc', targetEntity: Comment::class, orphanRemoval: true)]
    private Collection $comments;

    #[ORM\OneToMany(mappedBy: 'document', targetEntity: Notification::class,orphanRemoval: true)]
    private Collection $Notification;

    #[ORM\Column]
    private ?bool $IsAff = false;

    #[ORM\OneToMany(mappedBy: 'doc', targetEntity: Reminder::class, orphanRemoval: true)]
    private Collection $reminders;



    public function __construct()
    {
        $this->docVersions = new ArrayCollection();
        $this->comments = new ArrayCollection();
        $this->Notification = new ArrayCollection();
        $this->reminders = new ArrayCollection();
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

    public function getExtention(): ?string
    {
        return $this->extention;
    }

    public function setExtention(?string $extention): self
    {
        $this->extention = $extention;

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

    public function getStorageName(): ?string
    {
        return $this->storageName;
    }

    public function setStorageName(string $storageName): self
    {
        $this->storageName = $storageName;

        return $this;
    }

    public function getCreatedBy(): ?User
    {
        return $this->createdBy;
    }

    public function setCreatedBy(?User $createdBy): self
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    public function isIsDeleted(): ?bool
    {
        return $this->IsDeleted;
    }

    public function setIsDeleted(bool $IsDeleted): self
    {
        $this->IsDeleted = $IsDeleted;

        return $this;
    }

    
   

     /**
      * @return Collection<int, DocVersion>
      */
     public function getDocVersions(): Collection
     {
         return $this->docVersions;
     }

     public function addDocVersion(DocVersion $docVersion): self
     {
         if (!$this->docVersions->contains($docVersion)) {
             $this->docVersions->add($docVersion);
             $docVersion->setDoc($this);
         }

         return $this;
     }

     public function removeDocVersion(DocVersion $docVersion): self
     {
         if ($this->docVersions->removeElement($docVersion)) {
             // set the owning side to null (unless already changed)
             if ($docVersion->getDoc() === $this) {
                 $docVersion->setDoc(null);
             }
         }

         return $this;
     }

     public function getCurrentVersion(): ?int
     {
         return $this->currentVersion;
     }

     public function setCurrentVersion(int $currentVersion): self
     {
         $this->currentVersion = $currentVersion;

         return $this;
     }

     public function getCreatedAt(): ?\DateTimeImmutable
     {
         return $this->createdAt;
     }

     public function setCreatedAt(\DateTimeImmutable $createdAt): self
     {
         $this->createdAt = $createdAt;

         return $this;
     }

     public function getUpdatedAt(): ?\DateTimeImmutable
     {
         return $this->updatedAt;
     }

     public function setUpdatedAt(\DateTimeImmutable $updatedAt): self
     {
         $this->updatedAt = $updatedAt;

         return $this;
     }

     #[ORM\PrePersist()]
     
     public function onPrePersist() {
         $this->createdAt = new \DateTimeImmutable();
         $this->updatedAt = new \DateTimeImmutable();
     }
 
     #[ORM\PreUpdate()]
     public function onPreUpdate() {
         $this->updatedAt = new \DateTimeImmutable();
     }

     public function getTextContent(): ?string
     {
         return $this->textContent;
     }

     public function setTextContent(?string $textContent): self
     {
         $this->textContent = $textContent;

         return $this;
     }

     /**
      * @return Collection<int, Comment>
      */
     public function getComments(): Collection
     {
         return $this->comments;
     }

     public function addComment(Comment $comment): self
     {
         if (!$this->comments->contains($comment)) {
             $this->comments->add($comment);
             $comment->setDoc($this);
         }

         return $this;
     }

     public function removeComment(Comment $comment): self
     {
         if ($this->comments->removeElement($comment)) {
             // set the owning side to null (unless already changed)
             if ($comment->getDoc() === $this) {
                 $comment->setDoc(null);
             }
         }

         return $this;
     }

     /**
      * @return Collection<int, Notification>
      */
     public function getNotification(): Collection
     {
         return $this->Notification;
     }

     public function addNotification(Notification $notification): self
     {
         if (!$this->Notification->contains($notification)) {
             $this->Notification->add($notification);
             $notification->setDocument($this);
         }

         return $this;
     }

     public function removeNotification(Notification $notification): self
     {
         if ($this->Notification->removeElement($notification)) {
             // set the owning side to null (unless already changed)
             if ($notification->getDocument() === $this) {
                 $notification->setDocument(null);
             }
         }

         return $this;
     }

     public function isIsAff(): ?bool
     {
         return $this->IsAff;
     }

     public function setIsAff(bool $IsAff): self
     {
         $this->IsAff = $IsAff;

         return $this;
     }

     /**
      * @return Collection<int, Reminder>
      */
     public function getReminders(): Collection
     {
         return $this->reminders;
     }

     public function addReminder(Reminder $reminder): self
     {
         if (!$this->reminders->contains($reminder)) {
             $this->reminders->add($reminder);
             $reminder->setDoc($this);
         }

         return $this;
     }

     public function removeReminder(Reminder $reminder): self
     {
         if ($this->reminders->removeElement($reminder)) {
             // set the owning side to null (unless already changed)
             if ($reminder->getDoc() === $this) {
                 $reminder->setDoc(null);
             }
         }

         return $this;
     }

}
