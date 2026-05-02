<?php

namespace App\Entity;

use App\Repository\OdontogramDetailRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: OdontogramDetailRepository::class)]
class OdontogramDetail
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['odontogram:read', 'patient:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'odontogramDetails')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['patient:read'])]
    private ?Odontogram $odontogram = null;

    #[ORM\ManyToOne(inversedBy: 'odontogramDetails')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['odontogram:read', 'patient:read'])]
    private ?Tooth $tooth = null;

    #[ORM\ManyToOne(inversedBy: 'odontogramDetails')]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['odontogram:read', 'patient:read'])]
    private ?Pathology $pathology = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['odontogram:read', 'patient:read'])]
    private ?Treatment $treatment = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['odontogram:read', 'patient:read'])]
    private ?Status $status = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['odontogram:read', 'patient:read'])]
    private ?string $notes = null;

    #[ORM\Column(length: 50, nullable: true)]
    #[Groups(['odontogram:read', 'patient:read'])]
    private ?string $cara = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOdontogram(): ?Odontogram
    {
        return $this->odontogram;
    }

    public function setOdontogram(?Odontogram $odontogram): static
    {
        $this->odontogram = $odontogram;

        return $this;
    }

    public function getTooth(): ?Tooth
    {
        return $this->tooth;
    }

    public function setTooth(?Tooth $tooth): static
    {
        $this->tooth = $tooth;

        return $this;
    }

    public function getPathology(): ?Pathology
    {
        return $this->pathology;
    }

    public function setPathology(?Pathology $pathology): static
    {
        $this->pathology = $pathology;

        return $this;
    }

    public function getTreatment(): ?Treatment
    {
        return $this->treatment;
    }

    public function setTreatment(?Treatment $treatment): static
    {
        $this->treatment = $treatment;

        return $this;
    }

    public function getStatus(): ?Status
    {
        return $this->status;
    }

    public function setStatus(?Status $status): static
    {
        $this->status = $status;
        return $this;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): static
    {
        $this->notes = $notes;

        return $this;
    }

    public function getCara(): ?string
    {
        return $this->cara;
    }

    public function setCara(?string $cara): static
    {
        $this->cara = $cara;

        return $this;
    }
}
