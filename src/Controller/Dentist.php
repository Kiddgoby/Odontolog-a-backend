<?php

namespace App\Controller;

use App\Entity\Dentist;
use App\Repository\DentistRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/dentists')]
class DentistController extends AbstractController
{
    // ===========
    // LISTAR
    // ===========

    #[Route('', methods: ['GET'])]
    public function index(DentistRepository $repo): JsonResponse
    {
        return $this->json($repo->findAll());
    }

    // ===========
    // VER UNO
    // ===========

    #[Route('/{id}', methods: ['GET'])]
    public function show(Dentist $dentist): JsonResponse
    {
        return $this->json($dentist);
    }

    // ===========
    // CREAR
    // ===========

    #[Route('', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $dentist = new Dentist();
        $dentist->setFirstName($data['firstName']);
        $dentist->setLastName($data['lastName']);
        $dentist->setSpecialty($data['specialty']);
        $dentist->setAvailableDays($data['availableDays']);
        $dentist->setPhone($data['phone']);
        $dentist->setEmail($data['email']);

        $em->persist($dentist);
        $em->flush();

        return $this->json($dentist, 201);
    }

    // ===========
    // ACTUALIZAR
    // ===========

    #[Route('/{id}', methods: ['PUT'])]
    public function update(Dentist $dentist, Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $dentist->setFirstName($data['firstName']);
        $dentist->setLastName($data['lastName']);
        $dentist->setSpecialty($data['specialty']);
        $dentist->setAvailableDays($data['availableDays']);
        $dentist->setPhone($data['phone']);
        $dentist->setEmail($data['email']);

        $em->flush();

        return $this->json($dentist);
    }

    // ===========
    // ELIMINAR
    // ===========

    #[Route('/{id}', methods: ['DELETE'])]
    public function delete(Dentist $dentist, EntityManagerInterface $em): JsonResponse
    {
        $em->remove($dentist);
        $em->flush();

        return $this->json(null, 204);
    }
}