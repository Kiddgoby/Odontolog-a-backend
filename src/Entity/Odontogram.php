<?php

namespace App\Entity;

use App\Repository\OdontogramRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: OdontogramRepository::class)]
class Odontogram
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['odontogram:read', 'patient:read', 'appointment:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'odontograms')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['odontogram:read'])]
    private ?Patient $patient = null;

    #[ORM\ManyToOne(inversedBy: 'odontograms')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['odontogram:read'])]
    private ?Appointment $appointment = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Groups(['odontogram:read', 'patient:read'])]
    private ?\DateTimeInterface $creationDate = null;

    /**
     * @var Collection<int, OdontogramDetail>
     */
    #[ORM\OneToMany(targetEntity: OdontogramDetail::class, mappedBy: 'odontogram')]
    #[Groups(['odontogram:read'])]
    private Collection $odontogramDetails;

    public function __construct()
    {
        $this->odontogramDetails = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPatient(): ?Patient
    {
        return $this->patient;
    }

    public function setPatient(?Patient $patient): static
    {
        $this->patient = $patient;

        return $this;
    }

    public function getAppointment(): ?Appointment
    {
        return $this->appointment;
    }

    public function setAppointment(?Appointment $appointment): static
    {
        $this->appointment = $appointment;

        return $this;
    }

    public function getCreationDate(): ?\DateTimeInterface
    {
        return $this->creationDate;
    }

    public function setCreationDate(\DateTimeInterface $creationDate): static
    {
        $this->creationDate = $creationDate;

        return $this;
    }

    /**
     * @return Collection<int, OdontogramDetail>
     */
    public function getOdontogramDetails(): Collection
    {
        return $this->odontogramDetails;
    }

    public function addOdontogramDetail(OdontogramDetail $odontogramDetail): static
    {
        if (!$this->odontogramDetails->contains($odontogramDetail)) {
            $this->odontogramDetails->add($odontogramDetail);
            $odontogramDetail->setOdontogram($this);
        }

        return $this;
    }

    public function removeOdontogramDetail(OdontogramDetail $odontogramDetail): static
    {
        if ($this->odontogramDetails->removeElement($odontogramDetail)) {
            // set the owning side to null (unless already changed)
            if ($odontogramDetail->getOdontogram() === $this) {
                $odontogramDetail->setOdontogram(null);
            }
        }

        return $this;
    }
}
