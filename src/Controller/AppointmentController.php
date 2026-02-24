<?php

namespace App\Controller;

use App\Entity\Appointment;
use App\Entity\Patient;
use App\Entity\Dentist;
use App\Entity\Box;
use App\Entity\Treatment;
use App\Repository\AppointmentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/appointments')]
class AppointmentController extends AbstractController
{
    #[Route('', methods: ['GET'])]
    public function index(AppointmentRepository $repo): JsonResponse
    {
        return $this->json($repo->findAll(), 200, [], ['groups' => 'appointment:read']);
    }

    #[Route('/{id}', methods: ['GET'])]
    public function show(Appointment $appointment): JsonResponse
    {
        return $this->json($appointment, 200, [], ['groups' => 'appointment:read']);
    }

    #[Route('', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $appointment = new Appointment();
        $this->mapDataToAppointment($appointment, $data, $em);

        $em->persist($appointment);
        $em->flush();

        return $this->json($appointment, 201, [], ['groups' => 'appointment:read']);
    }

    #[Route('/{id}', methods: ['PUT'])]
    public function update(Appointment $appointment, Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $this->mapDataToAppointment($appointment, $data, $em);

        $em->flush();

        return $this->json($appointment, 200, [], ['groups' => 'appointment:read']);
    }

    #[Route('/{id}', methods: ['DELETE'])]
    public function delete(Appointment $appointment, EntityManagerInterface $em): JsonResponse
    {
        $em->remove($appointment);
        $em->flush();

        return $this->json(null, 204);
    }

    private function mapDataToAppointment(Appointment $appointment, array $data, EntityManagerInterface $em): void
    {
        if (isset($data['patientId'])) {
            $patient = $em->getRepository(Patient::class)->find($data['patientId']);
            if ($patient) $appointment->setPatient($patient);
        }

        if (isset($data['dentistId'])) {
            $dentist = $em->getRepository(Dentist::class)->find($data['dentistId']);
            if ($dentist) $appointment->setDentist($dentist);
        }

        if (isset($data['boxId'])) {
            $box = $em->getRepository(Box::class)->find($data['boxId']);
            if ($box) $appointment->setBox($box);
        }

        if (isset($data['treatmentId'])) {
            $treatment = $em->getRepository(Treatment::class)->find($data['treatmentId']);
            if ($treatment) $appointment->setTreatment($treatment);
        }

        if (isset($data['visitDate'])) {
            $appointment->setVisitDate(new \DateTime($data['visitDate']));
        }

        if (isset($data['consultationReason'])) {
            $appointment->setConsultationReason($data['consultationReason']);
        }
    }
}
