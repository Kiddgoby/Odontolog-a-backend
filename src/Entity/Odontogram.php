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
    #[Groups(['odontogram:read', 'patient:read'])]
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
            if ($odontogramDetail->getOdontogram() === $this) {
                $odontogramDetail->setOdontogram(null);
            }
        }

        return $this;
    }

    #[Groups(['odontogram:read', 'patient:read'])]
    public function getTeeth(): array
    {
        $teeth = [];

        foreach ($this->odontogramDetails as $detail) {
            $tooth = $detail->getTooth();
            $toothNum = $tooth->getId();
            
            $desc = $tooth->getDescription();
            if (preg_match('/(?:Tooth )?(\d+)/', $desc, $matches)) {
                $toothNum = (int)$matches[1];
            } else {
                $toothNum = $tooth->getId();
            }

            if (!isset($teeth[$toothNum])) {
                $teeth[$toothNum] = [
                    'sections' => [],
                    'sectionNotes' => [],
                    'pathologyTypes' => [],
                    'treatmentTypes' => [],
                    'absent' => false,
                    'note' => '',
                ];
            }

            $face = $detail->getCara();
            $notes = $detail->getNotes();

            $faceMapping = [
                'Superior (Vestibular)'      => 's1',
                'Derecha'                    => 's2',
                'Dret'                       => 's2',
                'Inferior (Palatino/Lingual)' => 's3',
                'Izquierda'                  => 's4',
                'Esquerre'                   => 's4',
                'Centro (Oclusal)'           => 's5',
                'Centre (Oclusal)'           => 's5',
            ];

            if ($face === 'Ausencia' || $face === 'absent') {
                $teeth[$toothNum]['absent'] = true;
                continue;
            }
            $section = null;
            if (isset($faceMapping[$face])) {
                $section = $faceMapping[$face];
            } elseif (preg_match('/^s[1-5]$/', $face)) {
                $section = $face;
            }

            if ($section) {
                $statusName = $detail->getStatus() ? $detail->getStatus()->getName() : '';
                $color = (strtolower($statusName) === 'done') ? '#4d79ff' : '#ff4d4d';
                $teeth[$toothNum]['sections'][$section] = $color;

                $teeth[$toothNum]['statusId'][$section] = $detail->getStatus() ? $detail->getStatus()->getId() : null;

                if ($detail->getPathology()) {
                    $teeth[$toothNum]['pathologyId'][$section] = $detail->getPathology()->getId();
                    $teeth[$toothNum]['pathologyTypes'][$section] =
                        strtolower(str_replace(' ', '', $detail->getPathology()->getDescription()));
                }

                if ($detail->getTreatment()) {
                    $teeth[$toothNum]['treatmentId'][$section] = $detail->getTreatment()->getId();
                    $teeth[$toothNum]['treatmentTypes'][$section] =
                        strtolower(str_replace(' ', '', $detail->getTreatment()->getTreatmentName()));
                }

                if ($notes) {
                    $teeth[$toothNum]['sectionNotes'][$section] = $notes;
                }
            }

            if ($notes && empty($teeth[$toothNum]['note'])) {
                $teeth[$toothNum]['note'] = $notes;
            }
        }

        return $teeth;
    }

    #[Groups(['odontogram:read', 'patient:read'])]
    public function getNotes(): string
    {
        $allNotes = [];
        foreach ($this->odontogramDetails as $detail) {
            if ($detail->getNotes()) {
                $allNotes[] = $detail->getNotes();
            }
        }
        return implode(' | ', array_unique($allNotes));
    }
}
