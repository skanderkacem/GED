<?php

namespace App\Entity;

use App\Repository\GroupeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\HasLifecycleCallbacks()]
#[ORM\Entity(repositoryClass: GroupeRepository::class)]
#[UniqueEntity(fields: ['name'], message: 'There is already a group with this name')]

class Groupe
{
    
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private ?string $name = null;

    #[ORM\ManyToOne(inversedBy: 'createdGroups')]
    #[ORM\JoinColumn(nullable: true,onDelete:'SET NULL')]
    private ?User $creator = null;



    #[ORM\ManyToMany(targetEntity: AccessRight::class, mappedBy: 'groups')]
    private Collection $accessRights;

    #[ORM\ManyToMany(targetEntity: User::class, inversedBy: 'groupes')]
    private Collection $members;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;


   


    public function __construct()
    {
        $this->accessRights = new ArrayCollection();
        $this->members = new ArrayCollection();
       
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

    public function getCreator(): ?User
    {
        return $this->creator;
    }

    public function setCreator(?User $creator): self
    {
        $this->creator = $creator;

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
            $accessRight->addGroup($this);
        }

        return $this;
    }

    public function removeAccessRight(AccessRight $accessRight): self
    {
        if ($this->accessRights->removeElement($accessRight)) {
            $accessRight->removeGroup($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getMembers(): Collection
    {
        return $this->members;
    }

    public function addMember(User $member): self
    {
        if (!$this->members->contains($member)) {
            $this->members->add($member);
        }

        return $this;
    }

    public function removeMember(User $member): self
    {
        $this->members->removeElement($member);

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


}