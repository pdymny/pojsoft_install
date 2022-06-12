<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Table(name="records")
 * @ORM\Entity
 */
class Records
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $month;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $year;

    /**
     * @var bool
     *
     * @ORM\Column(name="vat", type="boolean")
     */
    private $vat; 

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $vat_date_start;  

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $vat_date_end; 

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $vat_counter_start;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $vat_counter_end;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $vat_period_start;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $vat_period_end;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $vat_km_end;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Vehicles", inversedBy="vehicleRecords")
     * @ORM\JoinColumn(nullable=false)
     */
    private $vehicle;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Drivers", inversedBy="driverRecords")
     * @ORM\JoinColumn(nullable=false)
     */
    private $driver;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\RecordsCourse", mappedBy="record", cascade={"persist"})
     * @ORM\OrderBy({"date" = "ASC"})
     */
    private $courses;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\RecordsCosts", mappedBy="record", cascade={"persist"})
     * @ORM\OrderBy({"date" = "ASC"})
     */
    private $costs;


    public function __construct()
    {
        $this->courses = new ArrayCollection();
        $this->costs = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMonth(): ?int
    {
        return $this->month;
    }

    public function setMonth(?int $month): self
    {
        $this->month = $month;

        return $this;
    }

    public function getYear(): ?int
    {
        return $this->year;
    }

    public function setYear(?int $year): self
    {
        $this->year = $year;

        return $this;
    }

    public function getVat()
    {
        return $this->vat;
    }

    public function setVat($vat)
    {
        $this->vat = $vat;

        return $this;
    } 

    public function getVatDateStart(): ?\DateTimeInterface
    {
        return $this->vat_date_start;
    }

    public function setVatDateStart($vat_date_start): self
    {
        $this->vat_date_start = $vat_date_start;

        return $this;
    }

    public function getVatDateEnd(): ?\DateTimeInterface
    {
        return $this->vat_date_end;
    }

    public function setVatDateEnd($vat_date_end): self
    {
        $this->vat_date_end = $vat_date_end;

        return $this;
    }
 
    public function getVatCounterStart(): ?int
    {
        return $this->vat_counter_start;
    }

    public function setVatCounterStart(?int $counter): self
    {
        $this->vat_counter_start = $counter;

        return $this;
    }

    public function getVatCounterEnd(): ?int
    {
        return $this->vat_counter_end;
    }

    public function setVatCounterEnd(?int $counter): self
    {
        $this->vat_counter_end = $counter;

        return $this;
    }

    public function getVatPeriodStart(): ?int
    {
        return $this->vat_period_start;
    }

    public function setVatPeriodStart(?int $counter): self
    {
        $this->vat_period_start = $counter;

        return $this;
    }

    public function getVatPeriodEnd(): ?int
    {
        return $this->vat_period_end;
    }

    public function setVatPeriodEnd(?int $counter): self
    {
        $this->vat_period_end = $counter;

        return $this;
    }

    public function getVatKmEnd(): ?int
    {
        return $this->vat_km_end;
    }

    public function setVatKmEnd(?int $counter): self
    {
        $this->vat_km_end = $counter;

        return $this;
    }

    public function getVehicle(): ?Vehicles
    {
        return $this->vehicle;
    }

    public function setVehicle(?Vehicles $vehicle): self
    {
        $this->vehicle = $vehicle;

        return $this;
    }

    public function getDriver(): ?Drivers
    {
        return $this->driver;
    }

    public function setDriver(?Drivers $driver): self
    {
        $this->driver = $driver;

        return $this;
    }

    public function getName() 
    {
        return $this->month.'/'.$this->year;
    }

    /**
     * @return Collection|RecordsCourse[]
     */
    public function getCourses(): Collection
    {
        return $this->courses;
    }

    public function addCourses(RecordsCourse $course): self
    {
        if (!$this->courses->contains($course)) {
            $this->courses[] = $course;
            $course->setCourse($this);
        }
        return $this;
    }

    public function removeCourses(RecordsCourse $course): self
    {
        if ($this->courses->contains($course)) {
            $this->courses->removeElement($course);
            // set the owning side to null (unless already changed)
            if ($course->getCourse() === $this) {
                $course->setCourse(null);
            }
        }
        return $this;
    }

    /**
     * @return Collection|RecordsCosts[]
     */
    public function getCosts(): Collection
    {
        return $this->costs;
    }

    public function addCosts(RecordsCosts $cost): self
    {
        if (!$this->costs->contains($cost)) {
            $this->costs[] = $cost;
            $cost->setCosts($this);
        }
        return $this;
    }

    public function removeCosts(RecordsCosts $cost): self
    {
        if ($this->costs->contains($cost)) {
            $this->costs->removeElement($cost);
            // set the owning side to null (unless already changed)
            if ($cost->getCosts() === $this) {
                $cost->setCosts(null);
            }
        }
        return $this;
    }

}
