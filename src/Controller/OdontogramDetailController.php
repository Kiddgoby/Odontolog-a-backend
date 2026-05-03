<?php

namespace App\Controller;

use App\Entity\OdontogramDetail;
use App\Entity\Odontogram;
use App\Entity\Tooth;
use App\Entity\Pathology;
use App\Entity\Treatment;
use App\Entity\Status;
use App\Repository\OdontogramDetailRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/odontogram-details')]
class OdontogramDetailController extends AbstractController
{
    #[Route('', methods: ['GET'])]
    public function index(OdontogramDetailRepository $repo): JsonResponse
    {
        return $this->json($repo->findAll());
    }

    #[Route('/{id}', methods: ['GET'])]
    public function show(OdontogramDetail $detail): JsonResponse
    {
        return $this->json($detail);
    }

    #[Route('', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        error_log("DEBUG: OdontogramDetail creation request received: " . json_encode($data));
        
        if (!isset($data['odontogramId'])) {
            return $this->json(['error' => 'No se ha proporcionado un ID de odontograma (odontogramId)'], 400);
        }

        if (!isset($data['toothId'])) {
            return $this->json(['error' => 'No se ha proporcionado un ID de diente (toothId)'], 400);
        }

        $tooth = $this->findTooth($data['toothId'], $em);
        if (!$tooth) {
            return $this->json(['error' => 'El diente con ID/descripción ' . $data['toothId'] . ' no existe'], 404);
        }

        $odontogram = $em->getRepository(Odontogram::class)->find($data['odontogramId']);
        if (!$odontogram) {
            return $this->json(['error' => 'El odontograma no existe'], 404);
        }

        $cara = $data['cara'] ?? ($data['face'] ?? null);

        // Buscar si ya existe un detalle para este odontograma, diente y cara
        $detail = $em->getRepository(OdontogramDetail::class)->findOneBy([
            'odontogram' => $odontogram,
            'tooth' => $tooth,
            'cara' => $cara
        ]);

        if (!$detail) {
            $detail = new OdontogramDetail();
            $detail->setOdontogram($odontogram);
            $detail->setTooth($tooth);
            if ($cara) $detail->setCara($cara);
        }

        $this->mapDataToDetail($detail, $data, $em);

        error_log("DEBUG: Final statusId to save: " . ($detail->getStatus() ? $detail->getStatus()->getId() : 'NULL'));
        error_log("DEBUG: Final toothId to save: " . ($detail->getTooth() ? $detail->getTooth()->getId() : 'NULL'));

        $em->persist($detail);
        try {
            $em->flush();
            error_log("DEBUG: OdontogramDetail created/updated successfully with ID: " . $detail->getId());
        } catch (\Exception $e) {
            error_log("DEBUG: Exception during flush: " . $e->getMessage());
            return $this->json(['error' => 'Database error: ' . $e->getMessage()], 500);
        }

        return $this->json($detail, 201, [], ['groups' => ['odontogram:read', 'patient:read']]);
    }

    #[Route('/{id}', methods: ['PUT'])]
    public function update(OdontogramDetail $detail, Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $this->mapDataToDetail($detail, $data, $em);

        $em->flush();

        return $this->json($detail);
    }

    #[Route('/{id}', methods: ['DELETE'])]
    public function delete(OdontogramDetail $detail, EntityManagerInterface $em): JsonResponse
    {
        $em->remove($detail);
        $em->flush();

        return $this->json(null, 204);
    }

    #[Route('/api/pathologies', methods: ['GET'])]
    public function getPathologies(EntityManagerInterface $em): JsonResponse
    {
        $pathologies = $em->getRepository(Pathology::class)->findAll();
        $data = array_map(function($p) {
            return [
                'id' => $p->getId(),
                'description' => $p->getDescription(),
                'key' => strtolower(str_replace(' ', '_', $p->getDescription())),
                'hex' => '#E53935'
            ];
        }, $pathologies);
        
        return $this->json($data);
    }

    #[Route('/api/treatments', methods: ['GET'])]
    public function getTreatments(EntityManagerInterface $em): JsonResponse
    {
        $treatments = $em->getRepository(Treatment::class)->findAll();
        $data = array_map(function($t) {
            return [
                'id' => $t->getId(),
                'name' => $t->getTreatmentName(),
                'key' => strtolower(str_replace(' ', '_', $t->getTreatmentName())),
                'hex' => '#26A69A'
            ];
        }, $treatments);
        
        return $this->json($data);
    }

    #[Route('/api/statuses', methods: ['GET'])]
    public function getStatuses(EntityManagerInterface $em): JsonResponse
    {
        $statuses = $em->getRepository(Status::class)->findAll();
        $data = array_map(function($s) {
            return [
                'id' => $s->getId(),
                'name' => $s->getName(),
                'key' => strtolower(str_replace(' ', '_', $s->getName())),
                'hex' => '#4d79ff'
            ];
        }, $statuses);
        
        return $this->json($data);
    }

    #[Route('/update-notes', methods: ['POST'])]
    public function updateNotes(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['odontogramId']) || !isset($data['toothId'])) {
            return $this->json(['error' => 'Missing required fields: odontogramId, toothId'], 400);
        }

        $tooth = $this->findTooth($data['toothId'], $em);
        if (!$tooth) {
            return $this->json(['error' => 'Tooth not found'], 404);
        }

        $detail = $em->getRepository(OdontogramDetail::class)->findOneBy([
            'odontogram' => $data['odontogramId'],
            'tooth' => $tooth
        ]);
        
        if (!$detail) {
            $detail = new OdontogramDetail();
            
            $odontogram = $em->getRepository(Odontogram::class)->find($data['odontogramId']);
            if (!$odontogram) {
                return $this->json(['error' => 'Odontogram not found'], 404);
            }
            $detail->setOdontogram($odontogram);
            $detail->setTooth($tooth);

            $em->persist($detail);
        }

        $this->mapDataToDetail($detail, $data, $em);
        $em->flush();

        return $this->json([
            'success' => true,
            'message' => 'Tooth detail updated successfully',
            'detail' => [
                'id' => $detail->getId(),
                'odontogramId' => $detail->getOdontogram()->getId(),
                'toothId' => $detail->getTooth()->getId(),
                'pathologyId' => $detail->getPathology() ? $detail->getPathology()->getId() : null,
                'treatmentId' => $detail->getTreatment() ? $detail->getTreatment()->getId() : null,
                'statusId' => $detail->getStatus() ? $detail->getStatus()->getId() : null,
                'notes' => $detail->getNotes(),
                'cara' => $detail->getCara()
            ]
        ]);
    }

    #[Route('/get-notes/{odontogramId}/{toothId}', methods: ['GET'])]
    public function getNotes(int $odontogramId, int $toothId, EntityManagerInterface $em): JsonResponse
    {
        $tooth = $this->findTooth($toothId, $em);
        if (!$tooth) {
            return $this->json(['error' => 'Tooth not found'], 404);
        }

        $detail = $em->getRepository(OdontogramDetail::class)->findOneBy([
            'odontogram' => $odontogramId,
            'tooth' => $tooth
        ]);

        if (!$detail) {
            return $this->json([
                'success' => true,
                'notes' => null,
                'message' => 'No notes found for this tooth'
            ]);
        }

        return $this->json([
            'success' => true,
            'notes' => $detail->getNotes(),
            'cara' => $detail->getCara(),
            'detailId' => $detail->getId()
        ]);
    }

    private function getColorForTreatment(string $name): string
    {
        return match (strtolower($name)) {
            'limpieza' => '#26A69A',
            'obsturacion' => '#1E88E5',
            'endodoncia' => '#8E24AA',
            'extraccion' => '#D32F2F',
            'blanqueamiento' => '#FDD835',
            default => '#9E9E9E',
        };
    }

    private function getColorForPathology(string $name): string
    {
        return match (strtolower($name)) {
            'caries' => '#E53935',
            'gingivitis' => '#FB8C00',
            'periodontitis' => '#8E24AA',
            'fractura' => '#5D4037',
            'ausencia' => '#212121',
            default => '#BDBDBD',
        };
    }

    #[Route('/update-cara', methods: ['POST'])]
    public function updateCara(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['odontogramId']) || !isset($data['toothId']) || !isset($data['cara'])) {
            return $this->json(['error' => 'Missing required fields: odontogramId, toothId, cara'], 400);
        }

        $tooth = $this->findTooth($data['toothId'], $em);
        if (!$tooth) {
            return $this->json(['error' => 'Tooth not found'], 404);
        }

        $detail = $em->getRepository(OdontogramDetail::class)->findOneBy([
            'odontogram' => $data['odontogramId'],
            'tooth' => $tooth,
            'cara' => $data['cara']
        ]);

        if (!$detail) {
            $detail = new OdontogramDetail();
            
            $odontogram = $em->getRepository(Odontogram::class)->find($data['odontogramId']);
            if (!$odontogram) {
                return $this->json(['error' => 'Odontogram not found'], 404);
            }
            $detail->setOdontogram($odontogram);
            $detail->setTooth($tooth);
            $detail->setCara($data['cara']);
            $em->persist($detail);
        }

        $detail->setCara($data['cara']);
        $em->flush();

        return $this->json([
            'success' => true,
            'message' => 'Face updated successfully',
            'detail' => [
                'id' => $detail->getId(),
                'odontogramId' => $detail->getOdontogram()->getId(),
                'toothId' => $detail->getTooth()->getId(),
                'cara' => $detail->getCara()
            ]
        ]);
    }

    private function findTooth($idOrNumber, EntityManagerInterface $em): ?Tooth
    {
        $tooth = $em->getRepository(Tooth::class)->findOneBy(['description' => (string)$idOrNumber]);
        
        if (!$tooth) {
            $tooth = $em->getRepository(Tooth::class)->find($idOrNumber);
        }
        
        if (!$tooth && is_numeric($idOrNumber)) {
             $tooth = $em->getRepository(Tooth::class)->findOneBy(['description' => "Tooth " . $idOrNumber]);
        }
        
        return $tooth;
    }

    private function mapDataToDetail(OdontogramDetail $detail, array $data, EntityManagerInterface $em): void
    {
        if (isset($data['odontogramId'])) {
            $odontogram = $em->getRepository(Odontogram::class)->find($data['odontogramId']);
            if ($odontogram) {
                $detail->setOdontogram($odontogram);
            }
        }

        if (isset($data['toothId'])) {
            $tooth = $this->findTooth($data['toothId'], $em);
            if ($tooth) {
                $detail->setTooth($tooth);
            }
        }

        if (array_key_exists('pathologyId', $data)) {
            $pathology = $data['pathologyId'] ? $em->getRepository(Pathology::class)->find($data['pathologyId']) : null;
            $detail->setPathology($pathology);
        }

        if (array_key_exists('treatmentId', $data)) {
            $treatment = $data['treatmentId'] ? $em->getRepository(Treatment::class)->find($data['treatmentId']) : null;
            $detail->setTreatment($treatment);
        }

        if (array_key_exists('statusId', $data) && $data['statusId']) {
            $status = $em->getRepository(Status::class)->find($data['statusId']);
            if ($status) {
                $detail->setStatus($status);
                if (strtolower($status->getName()) === 'absent') {
                    $detail->setPathology(null);
                    $detail->setTreatment(null);
                }
            }
        } elseif (!$detail->getStatus()) {
            $pendingStatus = $em->getRepository(Status::class)->findOneBy(['name' => 'Pending']);
            if ($pendingStatus) {
                $detail->setStatus($pendingStatus);
            }
        }

        if (isset($data['notes'])) {
            $detail->setNotes($data['notes']);
        }

        if (isset($data['cara'])) {
            $detail->setCara($data['cara']);
        } elseif (isset($data['face'])) {
            $detail->setCara($data['face']);
        }
    }
}
