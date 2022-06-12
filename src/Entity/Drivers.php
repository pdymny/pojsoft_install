<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Table(name="drivers")
 * @ORM\Entity
 */
class Drivers
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=124)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=124)
     */
    private $firstname;

    /**
     * @ORM\Column(type="string", length=124, nullable=true)
     */
    private $city;

    /**
     * @ORM\Column(type="string", length=10, nullable=true)
     */
    private $code_post;

    /**
     * @ORM\Column(type="string", length=124, nullable=true)
     */
    private $street;

    /**
     * @ORM\Column(type="string", length=12, nullable=true)
     */
    private $phone;

    /**
     * @ORM\Column(type="string", length=124, nullable=true)
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=200, nullable=true)
     */
    private $description;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Records", mappedBy="drive")
     */
    private $driveRecords;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\DriversAttachments", mappedBy="driver", cascade={"persist"})
     */
    private $attachments;

    /**
     * @ORM\Column(type="datetime")
     * @var \DateTime
     */
    private $updatedAt;


    public function __toString() {
        return $this->getFullName();
    }

    public function __construct()
    {
        $this->driveRecords = new ArrayCollection();
        $this->attachments = new ArrayCollection();
        $this->updatedAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(?string $firstname): self
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(string $city): self
    {
        $this->city = $city;

        return $this;
    }

    public function getCodePost(): ?string
    {
        return $this->code_post;
    }

    public function setCodePost(string $code_post): self
    {
        $this->code_post = $code_post;

        return $this;
    }

    public function getStreet(): ?string
    {
        return $this->street;
    }

    public function setStreet(string $street): self
    {
        $this->street = $street;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(string $phone): self
    {
        $this->phone = $phone;

        return $this;
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

    public function getFullName()
    {
        return $this->getFirstname().' '.$this->getName();
    }

    /**
     * @return Collection|Records[]
     */
    public function getDriveRecords(): Collection
    {
        return $this->driveRecords;
    }

        /**
     * @return Collection|Attachment[]
     */
    public function getAttachments(): Collection
    {
        return $this->attachments;
    }

    public function addAttachment(DriversAttachments $attachment): self
    {
        if (!$this->attachments->contains($attachment)) {
            $this->attachments[] = $attachment;
            $attachment->setDriver($this);
        }
        return $this;
    }

    public function removeAttachment(DriversAttachments $attachment): self
    {
        if ($this->attachments->contains($attachment)) {
            $this->attachments->removeElement($attachment);
            // set the owning side to null (unless already changed)
            if ($attachment->getDriver() === $this) {
                $attachment->setDriver(null);
            }
        }
        return $this;
    }
}
