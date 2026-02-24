<?php

namespace App\Controller;

use App\Entity\Box;
use App\Repository\BoxRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/boxes')]
class BoxController extends AbstractController
{
    #[Route('', methods: ['GET'])]
    public function index(BoxRepository $repo): JsonResponse
    {
        return $this->json($repo->findAll(), 200, [], ['groups' => 'box:read']);
    }

    #[Route('/{id}', methods: ['GET'])]
    public function show(Box $box): JsonResponse
    {
        return $this->json($box, 200, [], ['groups' => 'box:read']);
    }

    #[Route('', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $box = new Box();
        $box->setName($data['name']);
        $box->setCapacity($data['capacity']);
        $box->setStatus($data['status']);

        $em->persist($box);
        $em->flush();

        return $this->json($box, 201, [], ['groups' => 'box:read']);
    }

    #[Route('/{id}', methods: ['PUT'])]
    public function update(Box $box, Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $box->setName($data['name']);
        $box->setCapacity($data['capacity']);
        $box->setStatus($data['status']);

        $em->flush();

        return $this->json($box, 200, [], ['groups' => 'box:read']);
    }

    #[Route('/{id}', methods: ['DELETE'])]
    public function delete(Box $box, EntityManagerInterface $em): JsonResponse
    {
        $em->remove($box);
        $em->flush();

        return $this->json(null, 204);
    }
}
