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
        $treatment->setTreatmentName($data['treatmentName']);
        $treatment->setDescription($data['description'] ?? null);

        $em->persist($treatment);
        $em->flush();

        return $this->json($treatment, 201, [], ['groups' => 'treatment:read']);
    }

    #[Route('/{id}', methods: ['PUT'])]
    public function update(Treatment $treatment, Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $treatment->setTreatmentName($data['treatmentName']);
        $treatment->setDescription($data['description'] ?? null);

        $em->flush();

        return $this->json($treatment, 200, [], ['groups' => 'treatment:read']);
    }

    #[Route('/{id}', methods: ['DELETE'])]
    public function delete(Treatment $treatment, EntityManagerInterface $em): JsonResponse
    {
        $em->remove($treatment);
        $em->flush();

        return $this->json(null, 204);
    }
}
