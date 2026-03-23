<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_index')]
    public function index(): Response
    {
        return $this->render('index.html.twig');
    }

    #[Route('/pacientes', name: 'app_patients')]
    public function patients(): Response
    {
        return $this->render('patients.html.twig');
    }

    #[Route('/citas', name: 'app_appointments')]
    public function appointments(): Response
    {
        return $this->render('appointments.html.twig');
    }

    #[Route('/odontologos', name: 'app_dentists')]
    public function dentists(): Response
    {
        return $this->render('dentists.html.twig');
    }

    #[Route('/odontogramas', name: 'app_odontograms')]
    public function odontograms(): Response
    {
        return $this->render('odontograms.html.twig');
    }
}
