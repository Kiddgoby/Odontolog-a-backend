<?php

namespace App\Entity;

use App\Repository\DentistRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: DentistRepository::class)]
class Dentist implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['dentist:read', 'patient:read', 'appointment:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    #[Groups(['dentist:read', 'patient:read', 'appointment:read'])]
    private ?string $firstName = null;

    #[ORM\Column(length: 100)]
    #[Groups(['dentist:read', 'patient:read', 'appointment:read'])]
    private ?string $lastName = null;

    #[ORM\Column(length: 100)]
    #[Groups(['dentist:read', 'patient:read', 'appointment:read'])]
    private ?string $specialty = null;

    #[ORM\Column(length: 100)]
    #[Groups(['dentist:read'])]
    private ?string $availableDays = null;

    #[ORM\Column(length: 20)]
    #[Groups(['dentist:read'])]
    private ?string $phone = null;

    #[ORM\Column(length: 100)]
    #[Groups(['dentist:read'])]
    private ?string $email = null;

    #[ORM\Column(length: 255)]
    private ?string $password = null;

    /**
     * @var Collection<int, Appointment>
     */
    #[ORM\OneToMany(targetEntity: Appointment::class, mappedBy: 'dentist', orphanRemoval: true)]
    private Collection $appointments;

    #[ORM\ManyToOne(targetEntity: Box::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['dentist:read'])]
    private ?Box $box = null;

    #[ORM\ManyToOne(targetEntity: Pathology::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['dentist:read'])]
    private ?Pathology $pathology = null;

    public function __construct()
    {
        $this->appointments = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): static
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): static
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getSpecialty(): ?string
    {
        return $this->specialty;
    }

    public function setSpecialty(string $specialty): static
    {
        $this->specialty = $specialty;

        return $this;
    }

    public function getAvailableDays(): ?string
    {
        return $this->availableDays;
    }

    public function setAvailableDays(string $availableDays): static
    {
        $this->availableDays = $availableDays;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(string $phone): static
    {
        $this->phone = $phone;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

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
            $appointment->setDentist($this);
        }

        return $this;
    }

    public function removeAppointment(Appointment $appointment): static
    {
        if ($this->appointments->removeElement($appointment)) {
            // set the owning side to null (unless already changed)
            if ($appointment->getDentist() === $this) {
                $appointment->setDentist(null);
            }
        }

        return $this;
    }

    public function getBox(): ?Box
    {
        return $this->box;
    }

    public function setBox(?Box $box): static
    {
        $this->box = $box;

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

    public function getUserIdentifier(): string
    {
        return $this->email ?? '';
    }

    public function getRoles(): array
    {
        return ['ROLE_DENTIST'];
    }

    public function eraseCredentials(): void
    {
        // Not needed for this implementation
    }

    public function getSalt(): ?string
    {
        return null;
    }
}
