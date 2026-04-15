<?php

namespace App\Controller;

use App\Entity\Patient;
use App\Entity\Odontogram;
use App\Entity\OdontogramDetail;
use App\Entity\Tooth;
use App\Entity\Pathology;
use App\Entity\Appointment;
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
        $patient->setAge(isset($data['age']) ? (int) $data['age'] : null);
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
        $patient->setAge(array_key_exists('age', $data) ? (int) $data['age'] : $patient->getAge());
        $patient->setSocialSecurityNumber($data['socialSecurityNumber'] ?? $patient->getSocialSecurityNumber());
        $patient->setPhone($data['phone'] ?? $patient->getPhone());
        $patient->setEmail($data['email'] ?? $patient->getEmail());
        $patient->setAddress($data['address'] ?? $patient->getAddress());
        $patient->setBillingData($data['billingData'] ?? $patient->getBillingData());
        $patient->setHealthStatus($data['healthStatus'] ?? $patient->getHealthStatus());
        $patient->setFamilyHistory($data['familyHistory'] ?? $patient->getFamilyHistory());
        $patient->setLifestyleHabits($data['lifestyleHabits'] ?? $patient->getLifestyleHabits());
        $patient->setMedicationAllergies($data['medicationAllergies'] ?? $patient->getMedicationAllergies());
        if (array_key_exists('odontogram', $data)) {

            // Sincronizar con la tabla odontogram_detail
            $odontogramJson = $data['odontogram'];
            if (isset($odontogramJson['teeth']) && is_array($odontogramJson['teeth'])) {
                $odontogramRepo = $em->getRepository(Odontogram::class);
                $toothRepo = $em->getRepository(Tooth::class);
                $pathologyRepo = $em->getRepository(Pathology::class);

                // Buscamos un odontograma relacional existente para este paciente
                $relationalOdontogram = $odontogramRepo->findOneBy(['patient' => $patient], ['creationDate' => 'DESC']);

                // Si no existe, intentamos buscar su última cita para crear uno
                if (!$relationalOdontogram) {
                    $appointmentRepo = $em->getRepository(Appointment::class);
                    $latestAppointment = $appointmentRepo->findOneBy(['patient' => $patient], ['visitDate' => 'DESC']);

                    if ($latestAppointment) {
                        $relationalOdontogram = new Odontogram();
                        $relationalOdontogram->setPatient($patient);
                        $relationalOdontogram->setAppointment($latestAppointment);
                        $relationalOdontogram->setCreationDate(new \DateTime());
                        $em->persist($relationalOdontogram);
                    }
                }

                if ($relationalOdontogram) {
                    foreach ($odontogramJson['teeth'] as $toothId => $state) {
                        $isMarked = (!empty($state['sections'])) || (isset($state['absent']) && $state['absent']);
                        
                        $tooth = $toothRepo->find((int)$toothId);
                        if (!$tooth) continue;

                        $detail = $em->getRepository(OdontogramDetail::class)->findOneBy([
                            'odontogram' => $relationalOdontogram,
                            'tooth' => $tooth
                        ]);

                        if ($isMarked) {
                            if (!$detail) {
                                $detail = new OdontogramDetail();
                                $detail->setOdontogram($relationalOdontogram);
                                $detail->setTooth($tooth);
                                $em->persist($detail);
                            }

                            // Detectar color para asignar patología
                            $hexColor = null;
                            if (isset($state['absent']) && $state['absent']) {
                                $hexColor = '#000'; // Negro para ausencia
                            } elseif (!empty($state['sections'])) {
                                // Tomar el primer color que encontremos
                                $hexColor = reset($state['sections']);
                            }

                            if ($hexColor) {
                                $pathologyId = $this->getPathologyIdByColor($hexColor);
                                $pathology = $pathologyRepo->find($pathologyId);
                                if ($pathology) {
                                    $detail->setPathology($pathology);
                                }
                            }

                            // Sincronizar notas
                            $combinedNotes = [];
                            if (isset($state['note']) && !empty($state['note'])) {
                                $combinedNotes[] = $state['note'];
                            }
                            if (isset($state['sectionNotes']) && is_array($state['sectionNotes'])) {
                                foreach ($state['sectionNotes'] as $section => $secNote) {
                                    if (!empty($secNote)) {
                                        $combinedNotes[] = "Sec $section: $secNote";
                                    }
                                }
                            }

                            if (!empty($combinedNotes)) {
                                $detail->setNotes(implode(' | ', $combinedNotes));
                            } else {
                                $detail->setNotes(null);
                            }
                        } else {
                            if ($detail) {
                                $em->remove($detail);
                            }
                        }
                    }
                }
            }
        }

        $em->flush();

        return $this->json($patient, 200, [], ['groups' => 'patient:read']);
    }

    /**
     * Mapea un color hexadecimal a un ID de patología
     * id 1: Pendiente (Rojo)
     * id 2: Realizado (Azul)
     * id 3: Caries (Verde)
     * id 4: Sellado (Amarillo)
     * id 5: Ausencia (Negro)
     */
    private function getPathologyIdByColor(string $color): int
    {
        $color = strtolower(trim($color));
        $map = [
            '#ff4d4d' => 1, // Rojo -> Pendiente
            '#4d79ff' => 2, // Azul -> Realizado
            '#4dff88' => 3, // Verde -> Caries
            '#ffff4d' => 4, // Amarillo -> Sellado
            '#000'    => 5, // Negro -> Ausencia
            '#000000' => 5,
        ];

        return $map[$color] ?? 3; // Por defecto Caries si no coincide
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