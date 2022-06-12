<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="records_costs")
 * @ORM\Entity
 */
class RecordsCosts
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
    private $description;

    /**
     * @ORM\Column(type="string", length=200)
     */
    private $document;

    /**
     * @ORM\Column(type="decimal", scale=2)
     */
    private $value;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Records", inversedBy="costs")
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $text): self
    {
        $this->description = $text;

        return $this;
    }

    public function getDocument(): ?string
    {
        return $this->document;
    }

    public function setDocument(?string $text): self
    {
        $this->document = $text;

        return $this;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function setValue(?string $text): self
    {
        $this->value = $text;

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

}
