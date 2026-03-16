<?php

namespace App\Controller;

use App\Entity\Odontogram;
use App\Entity\Patient;
use App\Entity\Appointment;
use App\Repository\OdontogramRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/odontograms')]
class OdontogramController extends AbstractController
{
    #[Route('', methods: ['GET'])]
    public function index(OdontogramRepository $repo): JsonResponse
    {
        return $this->json($repo->findAll(), 200, [], ['groups' => 'odontogram:read']);
    }

    #[Route('/{id}', methods: ['GET'])]
    public function show(Odontogram $odontogram): JsonResponse
    {
        return $this->json($odontogram, 200, [], ['groups' => 'odontogram:read']);
    }

    #[Route('', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $odontogram = new Odontogram();
        $this->mapDataToOdontogram($odontogram, $data, $em);

        $em->persist($odontogram);
        $em->flush();

        return $this->json($odontogram, 201, [], ['groups' => 'odontogram:read']);
    }

    #[Route('/{id}', methods: ['PUT'])]
    public function update(Odontogram $odontogram, Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $this->mapDataToOdontogram($odontogram, $data, $em);

        $em->flush();

        return $this->json($odontogram, 200, [], ['groups' => 'odontogram:read']);
    }

    #[Route('/{id}', methods: ['DELETE'])]
    public function delete(Odontogram $odontogram, EntityManagerInterface $em): JsonResponse
    {
        $em->remove($odontogram);
        $em->flush();

        return $this->json(null, 204);
    }

    private function mapDataToOdontogram(Odontogram $odontogram, array $data, EntityManagerInterface $em): void
    {
        if (isset($data['patientId'])) {
            $patient = $em->getRepository(Patient::class)->find($data['patientId']);
            if ($patient) $odontogram->setPatient($patient);
        }

        if (isset($data['appointmentId'])) {
            $appointment = $em->getRepository(Appointment::class)->find($data['appointmentId']);
            if ($appointment) $odontogram->setAppointment($appointment);
        }

        if (isset($data['creationDate'])) {
            $odontogram->setCreationDate(new \DateTime($data['creationDate']));
        }
    }
}
