<?php

namespace App\Entity;

use App\Repository\StatusRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: StatusRepository::class)]
class Status
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['status:read', 'odontogram:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    #[Groups(['status:read', 'odontogram:read'])]
    private ?string $name = null;

    /**
     * @var Collection<int, OdontogramDetail>
     */
    #[ORM\OneToMany(targetEntity: OdontogramDetail::class, mappedBy: 'status')]
    private Collection $odontogramDetails;

    public function __construct()
    {
        $this->odontogramDetails = new ArrayCollection();
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
            $odontogramDetail->setStatus($this);
        }

        return $this;
    }

    public function removeOdontogramDetail(OdontogramDetail $odontogramDetail): static
    {
        if ($this->odontogramDetails->removeElement($odontogramDetail)) {
            if ($odontogramDetail->getStatus() === $this) {
                $odontogramDetail->setStatus(null);
            }
        }

        return $this;
    }
}
