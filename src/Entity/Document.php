<?php

namespace App\Entity;

use App\Repository\DocumentRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: DocumentRepository::class)]
class Document
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['document:read', 'patient:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'documents')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['document:read'])]
    private ?Patient $patient = null;

    #[ORM\Column(length: 100)]
    #[Groups(['document:read', 'patient:read'])]
    private ?string $type = null;

    #[ORM\Column(length: 255)]
    #[Groups(['document:read', 'patient:read'])]
    private ?string $fileUrl = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Groups(['document:read', 'patient:read'])]
    private ?\DateTimeInterface $captureDate = null;

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

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getFileUrl(): ?string
    {
        return $this->fileUrl;
    }

    public function setFileUrl(string $fileUrl): static
    {
        $this->fileUrl = $fileUrl;

        return $this;
    }

    public function getCaptureDate(): ?\DateTimeInterface
    {
        return $this->captureDate;
    }

    public function setCaptureDate(\DateTimeInterface $captureDate): static
    {
        $this->captureDate = $captureDate;

        return $this;
    }
}
