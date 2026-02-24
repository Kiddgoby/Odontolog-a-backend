<?php

namespace App\Controller;

use App\Entity\Patient;
use App\Repository\PatientRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/patients')]
class PatientController extends AbstractController
{
    // ===========
    // LISTAR
    // ===========

    #[Route('', methods: ['GET'])]
    public function index(PatientRepository $repo): JsonResponse
    {
        return $this->json($repo->findAll(), 200, [], ['groups' => 'patient:read']);
    }

    // ===========
    // VER UNO
    // ===========

    #[Route('/{id}', methods: ['GET'])]
    public function show(Patient $patient): JsonResponse
    {
        return $this->json($patient, 200, [], ['groups' => 'patient:read']);
    }

    // ===========
    // CREAR
    // ===========

    #[Route('', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $patient = new Patient();
        $patient->setFirstName($data['firstName'] ?? '');
        $patient->setLastName($data['lastName'] ?? '');
        $patient->setNationalId($data['nationalId'] ?? 0);
        $patient->setSocialSecurityNumber($data['socialSecurityNumber'] ?? '');
        $patient->setPhone($data['phone'] ?? '');
        $patient->setEmail($data['email'] ?? '');
        $patient->setAddress($data['address'] ?? '');
        $patient->setBillingData($data['billingData'] ?? '');
        $patient->setHealthStatus($data['healthStatus'] ?? '');
        $patient->setFamilyHistory($data['familyHistory'] ?? '');
        $patient->setLifestyleHabits($data['lifestyleHabits'] ?? '');
        $patient->setMedicationAllergies($data['medicationAllergies'] ?? '');
        $patient->setRegistrationDate(new \DateTime());

        $em->persist($patient);
        $em->flush();

        return $this->json($patient, 201, [], ['groups' => 'patient:read']);
    }

    // ===========
    // ACTUALIZAR
    // ===========

    #[Route('/{id}', methods: ['PUT'])]
    public function update(Patient $patient, Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $patient->setFirstName($data['firstName'] ?? $patient->getFirstName());
        $patient->setLastName($data['lastName'] ?? $patient->getLastName());
        $patient->setNationalId($data['nationalId'] ?? $patient->getNationalId());
        $patient->setSocialSecurityNumber($data['socialSecurityNumber'] ?? $patient->getSocialSecurityNumber());
        $patient->setPhone($data['phone'] ?? $patient->getPhone());
        $patient->setEmail($data['email'] ?? $patient->getEmail());
        $patient->setAddress($data['address'] ?? $patient->getAddress());
        $patient->setBillingData($data['billingData'] ?? $patient->getBillingData());
        $patient->setHealthStatus($data['healthStatus'] ?? $patient->getHealthStatus());
        $patient->setFamilyHistory($data['familyHistory'] ?? $patient->getFamilyHistory());
        $patient->setLifestyleHabits($data['lifestyleHabits'] ?? $patient->getLifestyleHabits());
        $patient->setMedicationAllergies($data['medicationAllergies'] ?? $patient->getMedicationAllergies());

        $em->flush();

        return $this->json($patient, 200, [], ['groups' => 'patient:read']);
    }

    // ===========
    // ELIMINAR
    // ===========

    #[Route('/{id}', methods: ['DELETE'])]
    public function delete(Patient $patient, EntityManagerInterface $em): JsonResponse
    {
        $em->remove($patient);
        $em->flush();

        return $this->json(null, 204);
    }
}