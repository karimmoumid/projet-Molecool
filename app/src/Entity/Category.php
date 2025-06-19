<?php

namespace App\Entity;

use App\Repository\CategoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CategoryRepository::class)]
class Category
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $description = null;

    /**
     * @var Collection<int, Analysis>
     */
    #[ORM\OneToMany(targetEntity: Analysis::class, mappedBy: 'category', orphanRemoval: true)]
    private Collection $analysis;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $preview = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $equipment = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $deadline = null;

    /**
     * @var Collection<int, Appointment>
     */
    #[ORM\ManyToMany(targetEntity: Appointment::class, mappedBy: 'categories')]
    private Collection $appointment;

    public function __construct()
    {
        $this->analysis = new ArrayCollection();
        $this->appointment = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return Collection<int, Analysis>
     */
    public function getAnalysis(): Collection
    {
        return $this->analysis;
    }

    public function addAnalysi(Analysis $analysi): static
    {
        if (!$this->analysis->contains($analysi)) {
            $this->analysis->add($analysi);
            $analysi->setCategory($this);
        }

        return $this;
    }

    public function removeAnalysi(Analysis $analysi): static
    {
        if ($this->analysis->removeElement($analysi)) {
            // set the owning side to null (unless already changed)
            if ($analysi->getCategory() === $this) {
                $analysi->setCategory(null);
            }
        }

        return $this;
    }

    public function getPreview(): ?string
    {
        return $this->preview;
    }

    public function setPreview(?string $preview): static
    {
        $this->preview = $preview;

        return $this;
    }

    public function getEquipment(): ?string
    {
        return $this->equipment;
    }

    public function setEquipment(?string $equipment): static
    {
        $this->equipment = $equipment;

        return $this;
    }

    public function getDeadline(): ?string
    {
        return $this->deadline;
    }

    public function setDeadline(?string $deadline): static
    {
        $this->deadline = $deadline;

        return $this;
    }

    /**
     * @return Collection<int, Appointment>
     */
    public function getAppointment(): Collection
    {
        return $this->appointment;
    }

    public function addAppointment(Appointment $appointment): static
    {
        if (!$this->appointment->contains($appointment)) {
            $this->appointment->add($appointment);
        }

        return $this;
    }

    public function removeAppointment(Appointment $appointment): static
    {
        $this->appointment->removeElement($appointment);

        return $this;
    }
}
