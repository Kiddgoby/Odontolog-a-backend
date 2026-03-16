<?php

namespace App\Controller;

use App\Entity\Document;
use App\Entity\Patient;
use App\Repository\DocumentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/documents')]
class DocumentController extends AbstractController
{
    #[Route('', methods: ['GET'])]
    public function index(DocumentRepository $repo): JsonResponse
    {
        return $this->json($repo->findAll(), 200, [], ['groups' => 'document:read']);
    }

    #[Route('/{id}', methods: ['GET'])]
    public function show(Document $document): JsonResponse
    {
        return $this->json($document, 200, [], ['groups' => 'document:read']);
    }

    #[Route('', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $document = new Document();
        $this->mapDataToDocument($document, $data, $em);

        $em->persist($document);
        $em->flush();

        return $this->json($document, 201, [], ['groups' => 'document:read']);
    }

    #[Route('/{id}', methods: ['PUT'])]
    public function update(Document $document, Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $this->mapDataToDocument($document, $data, $em);

        $em->flush();

        return $this->json($document, 200, [], ['groups' => 'document:read']);
    }

    #[Route('/{id}', methods: ['DELETE'])]
    public function delete(Document $document, EntityManagerInterface $em): JsonResponse
    {
        $em->remove($document);
        $em->flush();

        return $this->json(null, 204);
    }

    private function mapDataToDocument(Document $document, array $data, EntityManagerInterface $em): void
    {
        if (isset($data['patientId'])) {
            $patient = $em->getRepository(Patient::class)->find($data['patientId']);
            if ($patient) $document->setPatient($patient);
        }

        if (isset($data['type'])) {
            $document->setType($data['type']);
        }

        if (isset($data['fileUrl'])) {
            $document->setFileUrl($data['fileUrl']);
        }

        if (isset($data['captureDate'])) {
            $document->setCaptureDate(new \DateTime($data['captureDate']));
        }
    }
}
