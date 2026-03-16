<?php

namespace App\Controller;

use App\Entity\Pathology;
use App\Repository\PathologyRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/pathologies')]
class PathologyController extends AbstractController
{
    #[Route('', methods: ['GET'])]
    public function index(PathologyRepository $repo): JsonResponse
    {
        return $this->json($repo->findAll(), 200, [], ['groups' => 'pathology:read']);
    }

    #[Route('/{id}', methods: ['GET'])]
    public function show(Pathology $pathology): JsonResponse
    {
        return $this->json($pathology, 200, [], ['groups' => 'pathology:read']);
    }

    #[Route('', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $pathology = new Pathology();
        $pathology->setDescription($data['description']);

        $em->persist($pathology);
        $em->flush();

        return $this->json($pathology, 201, [], ['groups' => 'pathology:read']);
    }

    #[Route('/{id}', methods: ['PUT'])]
    public function update(Pathology $pathology, Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $pathology->setDescription($data['description']);

        $em->flush();

        return $this->json($pathology, 200, [], ['groups' => 'pathology:read']);
    }

    #[Route('/{id}', methods: ['DELETE'])]
    public function delete(Pathology $pathology, EntityManagerInterface $em): JsonResponse
    {
        $em->remove($pathology);
        $em->flush();

        return $this->json(null, 204);
    }
}
