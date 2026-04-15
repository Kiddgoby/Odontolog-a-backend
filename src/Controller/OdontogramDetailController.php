<?php

namespace App\Controller;

use App\Entity\OdontogramDetail;
use App\Entity\Odontogram;
use App\Entity\Tooth;
use App\Entity\Pathology;
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

        $detail = new OdontogramDetail();
        $this->mapDataToDetail($detail, $data, $em);

        $em->persist($detail);
        $em->flush();

        return $this->json($detail, 201);
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

    #[Route('/update-notes', methods: ['POST'])]
    public function updateNotes(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // Logging para depuración
        error_log("DEBUG: Datos recibidos: " . json_encode($data));

        // Validar datos requeridos
        if (!isset($data['odontogramId']) || !isset($data['toothId']) || !isset($data['notes'])) {
            error_log("ERROR: Faltan campos requeridos");
            return $this->json(['error' => 'Missing required fields: odontogramId, toothId, notes'], 400);
        }

        error_log("DEBUG: Buscando detalle para odontogramaId={$data['odontogramId']}, toothId={$data['toothId']}");

        // Buscar si ya existe un detalle para este odontograma y diente
        $detail = $em->getRepository(OdontogramDetail::class)->findOneBy([
            'odontogram' => $data['odontogramId'],
            'tooth' => $data['toothId']
        ]);

        if (!$detail) {
            error_log("DEBUG: No existe detalle, creando nuevo");
            // Crear nuevo detalle si no existe
            $detail = new OdontogramDetail();
            
            // Asignar odontograma
            $odontogram = $em->getRepository(Odontogram::class)->find($data['odontogramId']);
            if (!$odontogram) {
                error_log("ERROR: Odontogram no encontrado con ID={$data['odontogramId']}");
                return $this->json(['error' => 'Odontogram not found'], 404);
            }
            $detail->setOdontogram($odontogram);
            error_log("DEBUG: Odontogram asignado correctamente");

            // Asignar diente
            $tooth = $em->getRepository(Tooth::class)->find($data['toothId']);
            if (!$tooth) {
                error_log("ERROR: Diente no encontrado con ID={$data['toothId']}");
                return $this->json(['error' => 'Tooth not found'], 404);
            }
            $detail->setTooth($tooth);
            error_log("DEBUG: Diente asignado correctamente - ID: {$tooth->getId()}, Description: {$tooth->getDescription()}");

            // Asignar patología por defecto (puedes ajustar esto según necesites)
            $pathology = $em->getRepository(Pathology::class)->find(1); // ID 1 como patología por defecto
            if ($pathology) {
                $detail->setPathology($pathology);
                error_log("DEBUG: Patología por defecto asignada");
            } else {
                error_log("WARNING: No se encontró patología con ID=1");
            }

            $em->persist($detail);
        } else {
            error_log("DEBUG: Detalle existente encontrado - ID: {$detail->getId()}");
        }

        // Actualizar las notas
        $detail->setNotes($data['notes']);
        error_log("DEBUG: Notas actualizadas: '{$data['notes']}'");
        
        $em->flush();
        error_log("DEBUG: Cambios guardados en base de datos");

        return $this->json([
            'success' => true,
            'message' => 'Notes updated successfully',
            'detail' => [
                'id' => $detail->getId(),
                'odontogramId' => $detail->getOdontogram()->getId(),
                'toothId' => $detail->getTooth()->getId(),
                'toothDescription' => $detail->getTooth()->getDescription(),
                'notes' => $detail->getNotes()
            ]
        ]);
    }

    #[Route('/get-notes/{odontogramId}/{toothId}', methods: ['GET'])]
    public function getNotes(int $odontogramId, int $toothId, EntityManagerInterface $em): JsonResponse
    {
        // Buscar el detalle para este odontograma y diente
        $detail = $em->getRepository(OdontogramDetail::class)->findOneBy([
            'odontogram' => $odontogramId,
            'tooth' => $toothId
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
            'detailId' => $detail->getId()
        ]);
    }

    private function mapDataToDetail(OdontogramDetail $detail, array $data, EntityManagerInterface $em): void
    {
        if (isset($data['odontogramId'])) {
            $odontogram = $em->getRepository(Odontogram::class)->find($data['odontogramId']);
            if ($odontogram) $detail->setOdontogram($odontogram);
        }

        if (isset($data['toothId'])) {
            $tooth = $em->getRepository(Tooth::class)->find($data['toothId']);
            if ($tooth) $detail->setTooth($tooth);
        }

        if (isset($data['pathologyId'])) {
            $pathology = $em->getRepository(Pathology::class)->find($data['pathologyId']);
            if ($pathology) $detail->setPathology($pathology);
        }

        if (isset($data['notes'])) {
            $detail->setNotes($data['notes']);
        }
    }
}
