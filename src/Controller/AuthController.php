<?php

namespace App\Controller;

use App\Entity\Patient;
use App\Repository\PatientRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Psr\Log\LoggerInterface;
use App\Repository\DentistRepository;

#[Route('/api/auth')]
class AuthController extends AbstractController
{
    #[Route('/login', methods: ['POST'])]
    public function login(
        Request $request,
        PatientRepository $patientRepo,
        DentistRepository $dentistRepo,
        LoggerInterface $logger
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);
        $email = $data['email'] ?? null;
        $password = $data['password'] ?? null;

        if (!$email || !$password) {
            return $this->json(['error' => 'Email y contraseña son requeridos'], 400);
        }

        // 1. Buscar en Pacientes
        $user = $patientRepo->findOneBy(['email' => $email]);

        // 2. Si no es paciente, buscar en Dentistas
        if (!$user) {
            $user = $dentistRepo->findOneBy(['email' => $email]);
        }

        // 3. Verificar si el usuario existe
        if (!$user) {
            return $this->json(['error' => 'Usuario no encontrado'], 401);
        }

        // 4. Comparación en texto plano (Sin Hash)
        if ($user->getPassword() !== $password) {
            return $this->json(['error' => 'Contraseña incorrecta'], 401);
        }

        // Generar respuesta
        $token = base64_encode($user->getEmail() . ':' . time());
        $roles = $user->getRoles();
        $role = !empty($roles) ? $roles[0] : 'ROLE_USER';

        return $this->json([
            'token' => $token,
            'role' => $role,
            'id' => $user->getId(),
            'firstName' => $user->getFirstName(),
            'lastName' => $user->getLastName(),
            'email' => $user->getEmail(),
        ], 200);
    }

    #[Route('/register', methods: ['POST'])]
    public function register(
        Request $request,
        EntityManagerInterface $em,
        PatientRepository $patientRepo
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);
        $email = $data['email'] ?? null;
        $password = $data['password'] ?? null;

        if (!$email || !$password) {
            return $this->json(['error' => 'Email y contraseña son requeridos'], 400);
        }

        if ($patientRepo->findOneBy(['email' => $email])) {
            return $this->json(['error' => 'El email ya está registrado'], 400);
        }

        $patient = new Patient();
        $patient->setEmail($email);
        $patient->setPassword($password); // Guardamos el texto plano directamente
        $patient->setFirstName($data['firstName'] ?? '');
        $patient->setLastName($data['lastName'] ?? '');
        $patient->setRegistrationDate(new \DateTime());
        // ... set de otros campos obligatorios que tengas

        $em->persist($patient);
        $em->flush();

        return $this->json(['status' => 'Usuario registrado en texto plano'], 201);
    }
}
