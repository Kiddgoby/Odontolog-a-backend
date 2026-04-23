<?php

namespace App\Controller;

use App\Entity\Treatment;
use App\Repository\TreatmentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/treatments')]
class TreatmentController extends AbstractController
{
    #[Route('', methods: ['GET'])]
    public function index(TreatmentRepository $repo): JsonResponse
    {
        return $this->json($repo->findAll(), 200, [], ['groups' => 'treatment:read']);
    }

    #[Route('/{id}', methods: ['GET'])]
    public function show(Treatment $treatment): JsonResponse
    {
        return $this->json($treatment, 200, [], ['groups' => 'treatment:read']);
    }

    #[Route('', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $treatment = new Treatment();
        $treatment->setTreatmentName($data['treatmentName'] ?? '');
        $treatment->setDescription($data['description'] ?? null);
        $treatment->setCategory($data['category'] ?? '');
        $treatment->setDuration(isset($data['duration']) ? (int) $data['duration'] : 0);
        $treatment->setPrice(isset($data['price']) ? (string) $data['price'] : '0.00');

        $em->persist($treatment);
        $em->flush();

        return $this->json($treatment, 201, [], ['groups' => 'treatment:read']);
    }

    #[Route('/{id}', methods: ['PUT'])]
    public function update(Treatment $treatment, Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $treatment->setTreatmentName($data['treatmentName'] ?? $treatment->getTreatmentName());
        $treatment->setDescription($data['description'] ?? $treatment->getDescription());
        $treatment->setCategory($data['category'] ?? $treatment->getCategory());
        $treatment->setDuration(isset($data['duration']) ? (int) $data['duration'] : $treatment->getDuration());
        $treatment->setPrice(isset($data['price']) ? (string) $data['price'] : $treatment->getPrice());

        $em->flush();

        return $this->json($treatment, 200, [], ['groups' => 'treatment:read']);
    }

    #[Route('/{id}', methods: ['DELETE'])]
    public function delete(Treatment $treatment, EntityManagerInterface $em): JsonResponse
    {
        try {
            // Doctrine manejará automáticamente la cascada de eliminación
            // Simplemente eliminamos el tratamiento y las citas asociadas se eliminarán automáticamente
            $em->remove($treatment);
            $em->flush();
            return $this->json(['message' => 'Tratamiento eliminado exitosamente'], 200);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }
}
