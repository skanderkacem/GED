<?php

namespace App\Entity;

use App\Entity\User;
use App\Entity\Groupe;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\FolderRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

#[ORM\HasLifecycleCallbacks()]
#[ORM\Entity(repositoryClass: FolderRepository::class)]
class Folder
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $name = null;

    #[ORM\ManyToOne(inversedBy: 'createdFolders')]
    #[ORM\JoinColumn(nullable: true,onDelete:'SET NULL')]
    private ?User $createdBy = null;

    #[ORM\ManyToOne(targetEntity: self::class, inversedBy: 'folders')]
    private ?self $parentFolder = null;

    #[ORM\OneToMany(mappedBy: 'parentFolder', targetEntity: self::class,cascade:["persist", "remove"], orphanRemoval:true)]
    private Collection $folders;

    #[ORM\Column]
    private ?bool $IsDeleted = false;

    #[ORM\OneToMany(mappedBy: 'folder', targetEntity: AccessRight::class, cascade:["persist", "remove"],orphanRemoval: true)]
    private Collection $accessRights;

    #[ORM\OneToMany(mappedBy: 'folder', targetEntity: Document::class, cascade:["persist", "remove"],orphanRemoval: true)]
    private Collection $documents;

    #[ORM\Column]
    private ?bool $IsAff = false;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\OneToMany(mappedBy: 'folder', targetEntity: Notification::class,orphanRemoval: true)]
    private Collection $notification;

  

    public function __construct()
    
    {   
      
        $this->folders = new ArrayCollection();
        $this->accessRights = new ArrayCollection();
        $this->documents = new ArrayCollection();
        $this->notification = new ArrayCollection();
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

    public function getCreatedBy(): ?User
    {
        return $this->createdBy;
    }

    public function setCreatedBy(?User $createdBy): self
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    public function getParentFolder(): ?self
    {
        return $this->parentFolder;
    }

    public function setParentFolder(?self $parentFolder): self
    {
        $this->parentFolder = $parentFolder;

        return $this;
    }

    /**
     * @return Collection<int, self>
     */
    public function getFolders(): Collection
    {
        return $this->folders;
    }

    public function addFolder(self $folder): self
    {
        if (!$this->folders->contains($folder)) {
            $this->folders->add($folder);
            $folder->setParentFolder($this);
        }

        return $this;
    }

    public function removeFolder(self $folder): self
    {
        if ($this->folders->removeElement($folder)) {
            // set the owning side to null (unless already changed)
            if ($folder->getParentFolder() === $this) {
                $folder->setParentFolder(null);
            }
        }

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
     * @return Collection<int, AccessRight>
     */
    public function getAccessRights(): Collection
    {
        return $this->accessRights;
    }

    public function addAccessRight(AccessRight $accessRight): self
    {
        if (!$this->accessRights->contains($accessRight)) {
            $this->accessRights->add($accessRight);
            $accessRight->setFolder($this);
        }

        return $this;
    }

    public function removeAccessRight(AccessRight $accessRight): self
    {
        if ($this->accessRights->removeElement($accessRight)) {
            // set the owning side to null (unless already changed)
            if ($accessRight->getFolder() === $this) {
                $accessRight->setFolder(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Document>
     */
    public function getDocuments(): Collection
    {
        return $this->documents;
    }

    public function addDocument(Document $document): self
    {
        if (!$this->documents->contains($document)) {
            $this->documents->add($document);
            $document->setFolder($this);
        }

        return $this;
    }

    public function removeDocument(Document $document): self
    {
        if ($this->documents->removeElement($document)) {
            // set the owning side to null (unless already changed)
            if ($document->getFolder() === $this) {
                $document->setFolder(null);
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

    public function isAncestor(Folder $folder):bool{
        $parent=$folder ;
        while($parent)
            {
                if($parent==$this) return true ;
                else  $parent=$parent->getParentFolder() ;
            }
        return false ;
    }

    public function hasAncestorCreatedBy(User $user):bool{
        $parent=$this ;
        while($parent)
            {
                if($parent->getCreatedBy()==$user) return true ;
                else  $parent= $parent->getParentFolder() ;
            }
        return false ;
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

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): self
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

    /**
     * @return Collection<int, Notification>
     */
    public function getNotification(): Collection
    {
        return $this->notification;
    }

    public function addNotification(Notification $notification): self
    {
        if (!$this->notification->contains($notification)) {
            $this->notification->add($notification);
            $notification->setFolder($this);
        }

        return $this;
    }

    public function removeNotification(Notification $notification): self
    {
        if ($this->notification->removeElement($notification)) {
            // set the owning side to null (unless already changed)
            if ($notification->getFolder() === $this) {
                $notification->setFolder(null);
            }
        }

        return $this;
    }
}