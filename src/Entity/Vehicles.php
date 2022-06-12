<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;


/**
 * @ORM\Table(name="vehicles")
 * @ORM\Entity
 */
class Vehicles
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=25)
     */
    private $registration;

    /**
     * @ORM\Column(type="string", length=25)
     */
    private $vin;

    /**
     * @ORM\Column(type="string", length=25, nullable=true)
     */
    private $first_registration;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $type;

    /**
     * @var bool
     *
     * @ORM\Column(name="counting_course", type="boolean")
     */
    private $counting_course; 

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $course;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $date_overview;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $date_insurance;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $date_oil;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $date_warranty;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $date_udt;  

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $date_mechanic;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $date_documents;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @var bool
     *
     * @ORM\Column(name="goal_vat", type="boolean")
     */
    private $goal_vat; 

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $date_commencement_records;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $state_counter;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Records", mappedBy="vehicle")
     */
    private $vehicleRecords;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\VehiclesAttachments", mappedBy="vehicle", cascade={"persist"})
     */
    private $attachments;

    /**
     * @ORM\Column(type="datetime")
     * @var \DateTime
     */
    private $updatedAt;


    public function __toString() {
        return $this->name;
    }

    public function __construct()
    {
        $this->vehicleRecords = new ArrayCollection();
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

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getRegistration(): ?string
    {
        return $this->registration;
    }

    public function setRegistration(string $registration): self
    {
        $this->registration = $registration;

        return $this;
    }

    public function getVIN(): ?string
    {
        return $this->vin;
    }

    public function setVIN(string $vin): self
    {
        $this->vin = $vin;

        return $this;
    }

    public function getFirstRegistration(): ?string
    {
        return $this->first_registration;
    }

    public function setFirstRegistration(string $first_registration): self
    {
        $this->first_registration = $first_registration;

        return $this;
    }

    public function getType(): ?int
    {
        return $this->type;
    }

    public function setType(?string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getCountingCourse()
    {
        return $this->counting_course;
    }

    public function setCountingCourse($counting_course)
    {
        $this->counting_course = $counting_course;

        return $this;
    } 

    public function getCourse(): ?int
    {
        return $this->course;
    }

    public function setCourse(?int $course): self
    {
        $this->course = $course;

        return $this;
    }  

    public function getDateOverview(): ?\DateTimeInterface
    {
        return $this->date_overview;
    }

    public function setDateOverview($date_overview): self
    {
        $this->date_overview = $date_overview;

        return $this;
    }

    public function getDateInsurance(): ?\DateTimeInterface
    {
        return $this->date_insurance;
    }

    public function setDateInsurance($date_insurance): self
    {
        $this->date_insurance = $date_insurance;

        return $this;
    }

    public function getDateOil(): ?\DateTimeInterface
    {
        return $this->date_oil;
    }

    public function setDateOil($date_oil): self
    {
        $this->date_oil = $date_oil;

        return $this;
    }

    public function getDateUdt(): ?\DateTimeInterface
    {
        return $this->date_udt;
    }

    public function setDateUdt($date_udt): self
    {
        $this->date_udt = $date_udt;

        return $this;
    }

    public function getDateWarranty(): ?\DateTimeInterface
    {
        return $this->date_warranty;
    }

    public function setDateWarranty($date_warranty): self
    {
        $this->date_warranty = $date_warranty;

        return $this;
    }

    public function getDateMechanic(): ?\DateTimeInterface
    {
        return $this->date_mechanic;
    }

    public function setDateMechanic($date_mechanic): self
    {
        $this->date_mechanic = $date_mechanic;

        return $this;
    }

    public function getDateDocuments(): ?\DateTimeInterface
    {
        return $this->date_documents;
    }

    public function setDateDocuments($date_documents): self
    {
        $this->date_documents = $date_documents;

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

    public function getGoalVat()
    {
        return $this->goal_vat;
    }

    public function setGoalVat($vat)
    {
        $this->goal_vat = $vat;

        return $this;
    } 

    public function getDateCommencementRecords(): ?\DateTimeInterface
    {
        return $this->date_commencement_records;
    }

    public function setDateCommencementRecords($date_commencement_records): self
    {
        $this->date_commencement_records = $date_commencement_records;

        return $this;
    } 

    public function getStateCounter(): ?int
    {
        return $this->state_counter;
    }

    public function setStateCounter(int $state_counter): self
    {
        $this->state_counter = $state_counter;

        return $this;
    } 

    /**
     * @return Collection|Attachment[]
     */
    public function getAttachments(): Collection
    {
        return $this->attachments;
    }

    public function addAttachment(VehiclesAttachments $attachment): self
    {
        if (!$this->attachments->contains($attachment)) {
            $this->attachments[] = $attachment;
            $attachment->setVehicle($this);
        }
        return $this;
    }

    public function removeAttachment(VehiclesAttachments $attachment): self
    {
        if ($this->attachments->contains($attachment)) {
            $this->attachments->removeElement($attachment);
            // set the owning side to null (unless already changed)
            if ($attachment->getVehicle() === $this) {
                $attachment->setVehicle(null);
            }
        }
        return $this;
    }

}
