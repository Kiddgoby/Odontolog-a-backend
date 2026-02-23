<?php

namespace App\Entity;

use App\Repository\PatientRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PatientRepository::class)]
class Patient
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $firstName = null;

    #[ORM\Column(length: 100)]
    private ?string $lastName = null;

    #[ORM\Column]
    private ?int $nationalId = null;

    #[ORM\Column(length: 20)]
    private ?string $socialSecurityNumber = null;

    #[ORM\Column(length: 20)]
    private ?string $phone = null;

    #[ORM\Column(length: 100)]
    private ?string $email = null;

    #[ORM\Column(length: 255)]
    private ?string $address = null;

    #[ORM\Column(length: 500)]
    private ?string $billingData = null;

    #[ORM\Column(length: 500)]
    private ?string $healthStatus = null;

    #[ORM\Column(length: 500)]
    private ?string $familyHistory = null;

    #[ORM\Column(length: 500)]
    private ?string $lifestyleHabits = null;

    #[ORM\Column(length: 500)]
    private ?string $medicationAllergies = null;

    #[ORM\Column]
    private ?\DateTime $registrationDate = null;

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

    public function getNationalId(): ?int
    {
        return $this->nationalId;
    }

    public function setNationalId(int $nationalId): static
    {
        $this->nationalId = $nationalId;

        return $this;
    }

    public function getSocialSecurityNumber(): ?string
    {
        return $this->socialSecurityNumber;
    }

    public function setSocialSecurityNumber(string $socialSecurityNumber): static
    {
        $this->socialSecurityNumber = $socialSecurityNumber;

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

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(string $address): static
    {
        $this->address = $address;

        return $this;
    }

    public function getBillingData(): ?string
    {
        return $this->billingData;
    }

    public function setBillingData(string $billingData): static
    {
        $this->billingData = $billingData;

        return $this;
    }

    public function getHealthStatus(): ?string
    {
        return $this->healthStatus;
    }

    public function setHealthStatus(string $healthStatus): static
    {
        $this->healthStatus = $healthStatus;

        return $this;
    }

    public function getFamilyHistory(): ?string
    {
        return $this->familyHistory;
    }

    public function setFamilyHistory(string $familyHistory): static
    {
        $this->familyHistory = $familyHistory;

        return $this;
    }

    public function getLifestyleHabits(): ?string
    {
        return $this->lifestyleHabits;
    }

    public function setLifestyleHabits(string $lifestyleHabits): static
    {
        $this->lifestyleHabits = $lifestyleHabits;

        return $this;
    }

    public function getMedicationAllergies(): ?string
    {
        return $this->medicationAllergies;
    }

    public function setMedicationAllergies(string $medicationAllergies): static
    {
        $this->medicationAllergies = $medicationAllergies;

        return $this;
    }

    public function getRegistrationDate(): ?\DateTime
    {
        return $this->registrationDate;
    }

    public function setRegistrationDate(\DateTime $registrationDate): static
    {
        $this->registrationDate = $registrationDate;

        return $this;
    }
}
