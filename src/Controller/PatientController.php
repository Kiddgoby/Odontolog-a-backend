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
            error_log("DEBUG: Odontogram JSON received: " . json_encode($odontogramJson));
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
                    error_log("DEBUG: Relational Odontogram ID: " . $relationalOdontogram->getId());
                    foreach ($odontogramJson['teeth'] as $toothId => $state) {
                        $isMarked = (!empty($state['sections'])) || 
                                    (isset($state['absent']) && $state['absent']) ||
                                    (!empty($state['note'])) ||
                                    (!empty($state['sectionNotes']));
                        
                        $tooth = $toothRepo->find((int)$toothId);
                        if (!$tooth) {
                            error_log("DEBUG: Tooth NOT found for ID: " . $toothId);
                            continue;
                        }

                        $detail = $em->getRepository(OdontogramDetail::class)->findOneBy([
                            'odontogram' => $relationalOdontogram,
                            'tooth' => $tooth
                        ]);

                        if ($isMarked) {
                            error_log("DEBUG: Processing tooth: " . $toothId);
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
                            } elseif (!$detail->getPathology()) {
                                // Si no tiene patología asignada aún y no hay color, asignar una por defecto (Caries)
                                $pathology = $pathologyRepo->find(3); 
                                if ($pathology) {
                                    $detail->setPathology($pathology);
                                }
                            }

                            // Sincronizar notas y cara
                            $combinedNotes = [];
                            $caras = [];
                            
                            $sectionMapping = [
                                's1' => 'Superior (Vestibular)',
                                's2' => 'Derecha',
                                's3' => 'Inferior (Palatino/Lingual)',
                                's4' => 'Izquierda',
                                's5' => 'Centro (Oclusal)',
                                'absent' => 'Ausencia'
                            ];

                            // Revisar si hay nota general por diente
                            if (isset($state['note']) && !empty($state['note'])) {
                                $note = $state['note'];
                                
                                // Buscar si la cara está en la nota (formato: "cara: Oclusal | ...")
                                if (preg_match('/Sec\s+(?:cara|face)\s*:\s*([^|]+)/i', $note, $matches)) {
                                    $caras[] = trim($matches[1]);
                                    // Remover la cara de la nota
                                    $note = preg_replace('/\s*\|\s*Sec\s+(?:cara|face)\s*:\s*[^|]+/i', '', $note);
                                    $note = preg_replace('/^Sec\s+(?:cara|face)\s*:\s*[^|]+\s*\|\s*/i', '', $note);
                                    if (!empty($note)) {
                                        $combinedNotes[] = $note;
                                    }
                                } else {
                                    $combinedNotes[] = $note;
                                }
                            }
                            
                            // 1. Recoger caras de las secciones marcadas (tengan nota o no)
                            if (isset($state['sections']) && is_array($state['sections'])) {
                                foreach ($state['sections'] as $section => $color) {
                                    $caras[] = $sectionMapping[$section] ?? $section;
                                }
                            }

                            // 2. Recoger notas de las secciones y añadir caras adicionales si existen
                            if (isset($state['sectionNotes']) && is_array($state['sectionNotes'])) {
                                foreach ($state['sectionNotes'] as $section => $secNote) {
                                    if (!empty($secNote)) {
                                        if (strtolower($section) === 'cara' || strtolower($section) === 'face') {
                                            $caras[] = $secNote;
                                        } else {
                                            // Si no estaba ya en caras, añadirla
                                            $faceName = $sectionMapping[$section] ?? $section;
                                            if (!in_array($faceName, $caras)) {
                                                $caras[] = $faceName;
                                            }
                                            $combinedNotes[] = $secNote;
                                        }
                                    }
                                }
                            }

                            // Si es ausencia, añadir a caras
                            if (isset($state['absent']) && $state['absent']) {
                                if (!in_array('Ausencia', $caras)) {
                                    $caras[] = 'Ausencia';
                                }
                            }

                            // Quitar duplicados en caras
                            $caras = array_unique($caras);

                            if (!empty($combinedNotes)) {
                                $detail->setNotes(implode(' | ', $combinedNotes));
                            } else {
                                $detail->setNotes(null);
                            }
                            
                            // Guardar cara en el campo face
                            if (!empty($caras)) {
                                $detail->setFace(implode(', ', $caras));
                            } else {
                                $detail->setFace(null);
                            }
                        } else {
                            if ($detail) {
                                $em->remove($detail);
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