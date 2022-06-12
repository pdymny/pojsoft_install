<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="records_course")
 * @ORM\Entity
 */
class RecordsCourse
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="date")
     */
    private $date;

    /**
     * @ORM\Column(type="string", length=200)
     */
    private $route_description;

    /**
     * @ORM\Column(type="string", length=200)
     */
    private $goal_trip;

    /**
     * @ORM\Column(type="integer")
     */
    private $km;

    /**
     * @ORM\Column(type="decimal", scale=4)
     */
    private $rate;

    /**
     * @ORM\Column(type="decimal", scale=2)
     */
    private $value;

    /**
     * @ORM\Column(type="integer")
     */
    private $counter;

    /**
     * @ORM\Column(type="string", length=300)
     */
    private $comments;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Records", inversedBy="courses")
     */
    private $record;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;
        return $this;
    }

    public function getRouteDescription(): ?string
    {
        return $this->route_description;
    }

    public function setRouteDescription(?string $text): self
    {
        $this->route_description = $text;

        return $this;
    }

    public function getGoalTrip(): ?string
    {
        return $this->goal_trip;
    }

    public function setGoalTrip(?string $text): self
    {
        $this->goal_trip = $text;

        return $this;
    }

    public function getKm(): ?int
    {
        return $this->km;
    }

    public function setKm(int $km): self
    {
        $this->km = $km;

        return $this;
    }

    public function getRate(): ?string
    {
        return $this->rate;
    }

    public function setRate(string $rate): self
    {
        $this->rate = $rate;

        return $this;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function setValue(string $value): self
    {
        $this->value = $value;

        return $this;
    }

    public function getCounter(): ?int
    {
        return $this->counter;
    }

    public function setCounter(int $counter): self
    {
        $this->counter = $counter;

        return $this;
    }

    public function getComments(): ?string
    {
        return $this->comments;
    }

    public function setComments(string $comments): self
    {
        $this->comments = $comments;

        return $this;
    }

    public function getRecord(): ?Records
    {
        return $this->record;
    }

    public function setRecord(?Records $record): self
    {
        $this->record = $record;
        return $this;
    }

    public function getDataEdit() 
    {
        $table = array('date' => $this->date, 'description' => $this->route_description, 'trip' => $this->goal_trip, 'km' => $this->km, 'rate' => $this->rate, 'value' => $this->value, 'counter' => $this->counter, 'id' => $this->id);

        return json_encode($table);
    }

}
