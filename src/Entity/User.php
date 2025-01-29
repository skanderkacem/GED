<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]

#[ORM\HasLifecycleCallbacks()]
#[UniqueEntity(fields: ['email'], message: 'There is already an account with this email')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    private ?string $email = null;

    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    

    #[ORM\Column(length: 50)]
    private ?string $firstName = null;

    #[ORM\Column(length: 50)]
    private ?string $lastName = null;

    #[ORM\Column(type: Types::BIGINT, nullable: true)]
    private ?string $phoneNumber = null;

    #[ORM\OneToMany(mappedBy: 'creator', targetEntity: Groupe::class,orphanRemoval: false,)]
    private Collection $createdGroups;

    #[ORM\OneToMany(mappedBy: 'createdBy', targetEntity: Folder::class,orphanRemoval: false)]
    private Collection $createdFolders;

    #[ORM\ManyToMany(targetEntity: AccessRight::class, mappedBy: 'users')]
    private Collection $accessRights;

    #[ORM\OneToMany(mappedBy: 'createdBy', targetEntity: Document::class,orphanRemoval: false)]
    private Collection $documents;

    #[ORM\ManyToMany(targetEntity: Groupe::class, mappedBy: 'members')]
    private Collection $groupes;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Comment::class, orphanRemoval: true)]
    private Collection $comments;


    #[ORM\Column(length: 255, nullable: true)]
    private ?string $photo = null;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Notification::class, orphanRemoval: true)]
    private Collection $notification;

    #[ORM\ManyToMany(targetEntity: Folder::class)]
    private Collection $favorites;

   

    public function __construct()
    {
        $this->createdGroups = new ArrayCollection();
        $this->createdFolders = new ArrayCollection();
        $this->accessRights = new ArrayCollection();
        $this->documents = new ArrayCollection();
        $this->groupes = new ArrayCollection();
        $this->comments = new ArrayCollection();
        $this->notification = new ArrayCollection();
        $this->favorites = new ArrayCollection();
      
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        
        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }



    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getPhoneNumber(): ?string
    {
        return $this->phoneNumber;
    }

    public function setPhoneNumber(?string $phoneNumber): self
    {
        $this->phoneNumber = $phoneNumber;

        return $this;
    }

    /**
     * @return Collection<int, Groupe>
     */
    public function getCreatedGroups(): Collection
    {
        return $this->createdGroups;
    }

    public function addCreatedGroup(Groupe $createdGroup): self
    {
        if (!$this->createdGroups->contains($createdGroup)) {
            $this->createdGroups->add($createdGroup);
            $createdGroup->setCreator($this);
        }

        return $this;
    }

    public function removeCreatedGroup(Groupe $createdGroup): self
    {
        if ($this->createdGroups->removeElement($createdGroup)) {
            // set the owning side to null (unless already changed)
            if ($createdGroup->getCreator() === $this) {
                $createdGroup->setCreator(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Folder>
     */
    public function getCreatedFolders(): Collection
    {
        return $this->createdFolders;
    }

    public function addCreatedFolder(Folder $folder): self
    {
        if (!$this->createdFolders->contains($folder)) {
            $this->createdFolders->add($folder);
            $folder->setCreatedBy($this);
        }

        return $this;
    }

    public function removeCreatedFolder(Folder $folder): self
    {
        if ($this->createdFolders->removeElement($folder)) {
            // set the owning side to null (unless already changed)
            if ($folder->getCreatedBy() === $this) {
                $folder->setCreatedBy(null);
            }
        }

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
            $accessRight->addUser($this);
        }

        return $this;
    }

    public function removeAccessRight(AccessRight $accessRight): self
    {
        if ($this->accessRights->removeElement($accessRight)) {
            $accessRight->removeUser($this);
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
            $document->setCreatedBy($this);
        }

        return $this;
    }

    public function removeDocument(Document $document): self
    {
        if ($this->documents->removeElement($document)) {
            // set the owning side to null (unless already changed)
            if ($document->getCreatedBy() === $this) {
                $document->setCreatedBy(null);
            }
        }

        return $this;
    }



    /**
     * @return Collection<int, Groupe>
     */
    public function getGroupes(): Collection
    {
        return $this->groupes;
    }

    public function addGroupe(Groupe $groupe): self
    {
        if (!$this->groupes->contains($groupe)) {
            $this->groupes->add($groupe);
            $groupe->addMember($this);
        }

        return $this;
    }

    public function removeGroupe(Groupe $groupe): self
    {
        if ($this->groupes->removeElement($groupe)) {
            $groupe->removeMember($this);
        }

        return $this;
    }
    public function hasAccessOnFolder(Folder $folder)
    {
        $accessRights=  $this->getAccessRights();
        $myFolders=$this->getCreatedFolders();


        for($i=0;$i<count($myFolders);$i++)
        {
            if( ( $folder==$myFolders[$i]or $folder->hasAncestorCreatedBy($this)) )    
                {
                    return 'creator' ;
                }
        }

        for($i=0;$i<count($accessRights);$i++)
        {
            if($accessRights[$i]->getMode()==1 and ( $folder==$accessRights[$i]->getFolder() or $accessRights[$i]->getFolder()->isAncestor($folder) ))    
                {
                    return 'write' ;
                }
        }
        $groupes=$this->getGroupes();
        for($k=0;$k<count($groupes);$k++)
        {   
            $accessRights=  $groupes[$k]->getAccessRights();
            for($i=0;$i<count($accessRights);$i++)
            {
                if($accessRights[$i]->getMode()==1 and ( $folder==$accessRights[$i]->getFolder() or $accessRights[$i]->getFolder()->isAncestor($folder) ))    
                    {
                        return 'write' ;
                    }
            }
    
            for($i=0;$i<count($accessRights);$i++)
            {
                if($accessRights[$i]->getMode()==2 and ( $folder==$accessRights[$i]->getFolder() or $accessRights[$i]->getFolder()->isAncestor($folder) ))    
                    {
                        return 'read' ;
                    }
            }
    
        }
        $accessRights=  $this->getAccessRights();

        for($i=0;$i<count($accessRights);$i++)
        {
            if($accessRights[$i]->getMode()==2 and ( $folder==$accessRights[$i]->getFolder() or $accessRights[$i]->getFolder()->isAncestor($folder) ))    
                {
                    return 'read' ;
                }
        }


    

        return 'none' ;
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

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeInterface $updatedAt): self
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
            $comment->setUser($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): self
    {
        if ($this->comments->removeElement($comment)) {
            // set the owning side to null (unless already changed)
            if ($comment->getUser() === $this) {
                $comment->setUser(null);
            }
        }

        return $this;
    }

    public function getPhoto(): ?string
    {
        return $this->photo;
    }

    public function setPhoto(?string $photo): self
    {
        $this->photo = $photo;

        return $this;
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
            $notification->setUser($this);
        }

        return $this;
    }

    public function removeNotification(Notification $notification): self
    {
        if ($this->notification->removeElement($notification)) {
            // set the owning side to null (unless already changed)
            if ($notification->getUser() === $this) {
                $notification->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Folder>
     */
    public function getFavorites(): Collection
    {
        return $this->favorites;
    }

    public function addFavorite(Folder $favorite): self
    {
        if (!$this->favorites->contains($favorite)) {
            $this->favorites->add($favorite);
        }

        return $this;
    }

    public function removeFavorite(Folder $favorite): self
    {
        $this->favorites->removeElement($favorite);

        return $this;
    }

public function __toString()
{
    return 'FullName : '.$this->firstName.' '.$this->lastName.' | Email : '.$this->email ;
}
}