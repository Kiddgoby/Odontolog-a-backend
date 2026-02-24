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
    #[Groups(['odontogram:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'odontogramDetails')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['odontogram:read'])]
    private ?Odontogram $odontogram = null;

    #[ORM\ManyToOne(inversedBy: 'odontogramDetails')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['odontogram:read'])]
    private ?Tooth $tooth = null;

    #[ORM\ManyToOne(inversedBy: 'odontogramDetails')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['odontogram:read'])]
    private ?Pathology $pathology = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['odontogram:read'])]
    private ?string $notes = null;

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

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): static
    {
        $this->notes = $notes;

        return $this;
    }
}
