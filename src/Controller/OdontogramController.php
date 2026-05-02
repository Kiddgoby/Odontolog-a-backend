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
    #[Route('/patient/{patientId}/latest', methods: ['GET'])]
    public function getLatestByPatient(int $patientId, OdontogramRepository $repo): JsonResponse
    {
        $odontogram = $repo->findOneBy(
            ['patient' => $patientId],
            ['creationDate' => 'DESC'] 
        );

        if (!$odontogram) {
            return $this->json(null, 404);
        }

        return $this->json($odontogram, 200, [], ['groups' => 'odontogram:read']);
    }

    #[Route('/get-or-create', methods: ['POST'])]
    public function getOrCreate(Request $request, EntityManagerInterface $em, OdontogramRepository $repo): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $patientId = $data['patientId'] ?? null;

        if (!$patientId) {
            return $this->json(['error' => 'patientId requerido'], 400);
        }

        $patient = $em->getRepository(Patient::class)->find($patientId);
        if (!$patient) {
            return $this->json(['error' => 'Paciente no encontrado'], 404);
        }

        $odontogram = $repo->findOneBy(
            ['patient' => $patient],
            ['creationDate' => 'DESC']
        );

        if (!$odontogram) {
            $odontogram = new Odontogram();
            $odontogram->setPatient($patient);
            $odontogram->setCreationDate(new \DateTime());
            
            $appointmentRepo = $em->getRepository(Appointment::class);
            $latestAppointment = $appointmentRepo->findOneBy(['patient' => $patient], ['visitDate' => 'DESC']);
            
            if ($latestAppointment) {
                $odontogram->setAppointment($latestAppointment);
            } else {
                error_log("DEBUG: No se encontró cita para el paciente " . $patientId . ". El guardado podría fallar si es obligatorio.");
                $anyAppointment = $appointmentRepo->findOneBy([]);
                if ($anyAppointment) $odontogram->setAppointment($anyAppointment);
            }

            $em->persist($odontogram);
            try {
                $em->flush();
            } catch (\Exception $e) {
                error_log("DEBUG: Error al crear odontograma: " . $e->getMessage());
                return $this->json(['error' => 'No se pudo crear el odontograma: ' . $e->getMessage()], 500);
            }
        }

        return $this->json($odontogram, 200, [], ['groups' => 'odontogram:read']);
    }
}

