<?php

namespace App\Controller;

use App\Entity\Patient;
use App\Entity\Odontogram;
use App\Entity\OdontogramDetail;
use App\Entity\Tooth;
use App\Entity\Pathology;
use App\Entity\Treatment;
use App\Entity\Status;
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

    #[Route('', methods: ['GET'])]
    public function index(PatientRepository $repo): JsonResponse
    {
        return $this->json($repo->findAll(), 200, [], ['groups' => 'patient:read']);
    }

    #[Route('/{id}', methods: ['GET'])]
    public function show(Patient $patient): JsonResponse
    {
        return $this->json($patient, 200, [], ['groups' => 'patient:read']);
    }



    #[Route('', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $patient = new Patient();
        $patient->setFirstName($data['firstName'] ?? '');
        $patient->setLastName($data['lastName'] ?? '');
        $patient->setNationalId($data['nationalId'] ?? '');
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

            $odontogramJson = $data['odontogram'];
            error_log("DEBUG: Odontogram JSON received: " . json_encode($odontogramJson));
            if (isset($odontogramJson['teeth']) && is_array($odontogramJson['teeth'])) {
                $odontogramRepo = $em->getRepository(Odontogram::class);
                $toothRepo = $em->getRepository(Tooth::class);
                $pathologyRepo = $em->getRepository(Pathology::class);

                $relationalOdontogram = $odontogramRepo->findOneBy(['patient' => $patient], ['creationDate' => 'DESC']);

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
                    $detailRepo = $em->getRepository(OdontogramDetail::class);
                    $toothRepo = $em->getRepository(Tooth::class);
                    $pathologyRepo = $em->getRepository(Pathology::class);
                    $treatmentRepo = $em->getRepository(Treatment::class);
                    $statusRepo = $em->getRepository(Status::class);

                    $sectionMapping = [
                        's1' => 'Superior (Vestibular)',
                        's2' => 'Derecha',
                        's3' => 'Inferior (Palatino/Lingual)',
                        's4' => 'Izquierda',
                        's5' => 'Centro (Oclusal)'
                    ];

                    foreach ($odontogramJson['teeth'] as $toothId => $state) {
                        $tooth = $toothRepo->find((int)$toothId);
                        if (!$tooth) {
                            $tooth = $toothRepo->findOneBy(['description' => "Tooth $toothId"]);
                        }
                        if (!$tooth) continue;

                        if (isset($state['absent']) && $state['absent']) {
                            $absentStatus = $statusRepo->findOneBy(['name' => 'Absent']);
                            $detail = $detailRepo->findOneBy(['odontogram' => $relationalOdontogram, 'tooth' => $tooth, 'face' => 'absent']);
                            if (!$detail) {
                                $detail = new OdontogramDetail();
                                $detail->setOdontogram($relationalOdontogram);
                                $detail->setTooth($tooth);
                                $detail->setFace('absent');
                                $em->persist($detail);
                            }
                            $detail->setStatus($absentStatus);
                            $detail->setPathology(null);
                            $detail->setTreatment(null);
                            $detail->setNotes($state['note'] ?? null);
                            
                            foreach (['s1','s2','s3','s4','s5'] as $s) {
                                $d = $detailRepo->findOneBy(['odontogram' => $relationalOdontogram, 'tooth' => $tooth, 'face' => $s]);
                                if ($d) $em->remove($d);
                            }
                            continue;
                        } else {
                            $absentDetail = $detailRepo->findOneBy(['odontogram' => $relationalOdontogram, 'tooth' => $tooth, 'face' => 'absent']);
                            if ($absentDetail) $em->remove($absentDetail);
                        }

                        foreach ($sectionMapping as $secKey => $secName) {
                            $hasSectionData = isset($state['sections'][$secKey]) || 
                                              isset($state['pathologyTypes'][$secKey]) || 
                                              isset($state['treatmentTypes'][$secKey]) ||
                                              isset($state['sectionNotes'][$secKey]);

                            $detail = $detailRepo->findOneBy([
                                'odontogram' => $relationalOdontogram,
                                'tooth' => $tooth,
                                'face' => $secKey
                            ]);

                            if ($hasSectionData) {
                                if (!$detail) {
                                    $detail = new OdontogramDetail();
                                    $detail->setOdontogram($relationalOdontogram);
                                    $detail->setTooth($tooth);
                                    $detail->setFace($secKey);
                                    $em->persist($detail);
                                }

                                if (isset($state['pathologyTypes'][$secKey])) {
                                    $pKey = $state['pathologyTypes'][$secKey];
                                    $pathology = $pathologyRepo->findOneBy(['description' => ucfirst($pKey)]);
                                    if ($pathology) $detail->setPathology($pathology);
                                }

                                if (isset($state['treatmentTypes'][$secKey])) {
                                    $tKey = $state['treatmentTypes'][$secKey];
                                    $treatment = $treatmentRepo->findOneBy(['treatmentName' => ucfirst($tKey)]);
                                    if ($treatment) $detail->setTreatment($treatment);
                                }

                                if (isset($state['sectionNotes'][$secKey])) {
                                    $detail->setNotes($state['sectionNotes'][$secKey]);
                                }

                                if (!$detail->getStatus()) {
                                    $pendingStatus = $statusRepo->findOneBy(['name' => 'Pending']);
                                    if ($pendingStatus) $detail->setStatus($pendingStatus);
                                }
                            } else {
                                if ($detail) $em->remove($detail);
                            }
                        }
                    }
                } else {
                    error_log("DEBUG: No relational odontogram found or created for patient.");
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
            '#ff4d4d' => 1,
            '#4d79ff' => 2, 
            '#4dff88' => 3, 
            '#ffff4d' => 4,
            '#000'    => 5, 
            '#000000' => 5,
        ];

        return $map[$color] ?? 3;
    }


    #[Route('/{id}', methods: ['DELETE'])]
    public function delete(Patient $patient, EntityManagerInterface $em): JsonResponse
    {
        $em->remove($patient);
        $em->flush();

        return $this->json(null, 204);
    }
}