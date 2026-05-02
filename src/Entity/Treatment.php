<?php

namespace App\Entity;

use App\Repository\TreatmentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedName;

#[ORM\Entity(repositoryClass: TreatmentRepository::class)]
class Treatment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['treatment:read', 'appointment:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 150)]
    #[Groups(['treatment:read', 'appointment:read', 'odontogram:read', 'patient:read'])]
    private ?string $treatmentName = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['treatment:read', 'appointment:read', 'odontogram:read', 'patient:read'])]
    private ?string $description = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Groups(['treatment:read', 'appointment:read'])]
    private ?string $category = null;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    #[Groups(['treatment:read', 'appointment:read'])]
    private ?int $duration = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    #[Groups(['treatment:read', 'appointment:read'])]
    private ?string $price = null;

    /**
     * @var Collection<int, Appointment>
     */
    #[ORM\OneToMany(targetEntity: Appointment::class, mappedBy: 'treatment', cascade: ['remove'])]
    private Collection $appointments;

    public function __construct()
    {
        $this->appointments = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTreatmentName(): ?string
    {
        return $this->treatmentName;
    }

    #[SerializedName('name')]
    #[Groups(['treatment:read', 'appointment:read', 'odontogram:read', 'patient:read'])]
    public function getName(): ?string
    {
        return $this->treatmentName;
    }

    public function setTreatmentName(string $treatmentName): static
    {
        $this->treatmentName = $treatmentName;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getCategory(): ?string
    {
        return $this->category;
    }

    public function setCategory(?string $category): static
    {
        $this->category = $category;

        return $this;
    }

    public function getDuration(): ?int
    {
        return $this->duration;
    }

    public function setDuration(?int $duration): static
    {
        $this->duration = $duration;

        return $this;
    }

    public function getPrice(): ?string
    {
        return $this->price;
    }

    public function setPrice(?string $price): static
    {
        $this->price = $price;

        return $this;
    }

    /**
     * @return Collection<int, Appointment>
     */
    public function getAppointments(): Collection
    {
        return $this->appointments;
    }

    public function addAppointment(Appointment $appointment): static
    {
        if (!$this->appointments->contains($appointment)) {
            $this->appointments->add($appointment);
            $appointment->setTreatment($this);
        }

        return $this;
    }

    public function removeAppointment(Appointment $appointment): static
    {
        if ($this->appointments->removeElement($appointment)) {
            // set the owning side to null (unless already changed)
            if ($appointment->getTreatment() === $this) {
                $appointment->setTreatment(null);
            }
        }

        return $this;
    }
}
