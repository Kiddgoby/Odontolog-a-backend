<?php

namespace App\Controller;

use App\Entity\Tooth;
use App\Repository\ToothRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/teeth')]
class ToothController extends AbstractController
{
    #[Route('', methods: ['GET'])]
    public function index(ToothRepository $repo): JsonResponse
    {
        return $this->json($repo->findAll(), 200, [], ['groups' => 'tooth:read']);
    }

    #[Route('/{id}', methods: ['GET'])]
    public function show(Tooth $tooth): JsonResponse
    {
        return $this->json($tooth, 200, [], ['groups' => 'tooth:read']);
    }

    #[Route('', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $tooth = new Tooth();
        $tooth->setDescription($data['description']);

        $em->persist($tooth);
        $em->flush();

        return $this->json($tooth, 201, [], ['groups' => 'tooth:read']);
    }

    #[Route('/{id}', methods: ['PUT'])]
    public function update(Tooth $tooth, Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $tooth->setDescription($data['description']);

        $em->flush();

        return $this->json($tooth, 200, [], ['groups' => 'tooth:read']);
    }

    #[Route('/{id}', methods: ['DELETE'])]
    public function delete(Tooth $tooth, EntityManagerInterface $em): JsonResponse
    {
        $em->remove($tooth);
        $em->flush();

        return $this->json(null, 204);
    }
}
