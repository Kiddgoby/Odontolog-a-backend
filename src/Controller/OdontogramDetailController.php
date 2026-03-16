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
