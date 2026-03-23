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

#[Route('/api/auth')]
class AuthController extends AbstractController
{
    #[Route('/login', methods: ['POST'])]
    public function login(
        Request $request,
        PatientRepository $patientRepo,
        UserPasswordHasherInterface $passwordHasher,
        LoggerInterface $logger
    ): JsonResponse {
        $logger->info('=== INICIANDO LOGIN ===');
        
        $data = json_decode($request->getContent(), true);
        $logger->info('Datos recibidos', ['data' => $data]);
        
        $email = $data['email'] ?? null;
        $password = $data['password'] ?? null;

        $logger->info("Email: $email, Password length: " . strlen($password ?? ''));

        if (!$email || !$password) {
            $logger->error('Email o contraseña vacíos');
            return $this->json(['error' => 'Email y contraseña son requeridos'], 400);
        }

        $logger->info("Buscando paciente con email: $email");
        
        $patient = $patientRepo->findOneBy(['email' => $email]);

        if (!$patient) {
            $logger->error('Paciente no encontrado para email: ' . $email);
            return $this->json(['error' => 'Email no encontrado'], 401);
        }

        $logger->info("Paciente encontrado: " . $patient->getFirstName());

        $storedPassword = $patient->getPassword();
        $logger->info("Password almacenada existe: " . ($storedPassword ? "SÍ" : "NO"));
        $logger->info("Password almacenada valor: '$storedPassword'");
        $logger->info("Password recibida valor: '$password'");
        $logger->info("Longitud almacenada: " . strlen($storedPassword ?? ''));
        $logger->info("Longitud recibida: " . strlen($password ?? ''));

        if (!$storedPassword) {
            $logger->error('Paciente sin contraseña asignada');
            return $this->json(['error' => 'Usuario sin contraseña'], 401);
        }

        // Comparar contraseña en texto plano
        $isPasswordValid = ($storedPassword === $password);
        $logger->info("Comparación === result: " . ($isPasswordValid ? "TRUE" : "FALSE"));
        $logger->info("Bytes almacenados: " . bin2hex($storedPassword));
        $logger->info("Bytes recibidos: " . bin2hex($password));

        if (!$isPasswordValid) {
            $logger->error('Contraseña incorrecta para email: ' . $email);
            return $this->json(['error' => 'Contraseña incorrecta'], 401);
        }

        $logger->info('Login exitoso para: ' . $email);

        // Generar un token simple (en producción usar JWT)
        $token = base64_encode($patient->getEmail() . ':' . time());
        
        // Obtener los roles del paciente
        $roles = $patient->getRoles();
        $role = !empty($roles) ? $roles[0] : 'ROLE_PATIENT';

        return $this->json([
            'token' => $token,
            'role' => $role,
            'id' => $patient->getId(),
            'firstName' => $patient->getFirstName(),
            'lastName' => $patient->getLastName(),
            'email' => $patient->getEmail(),
        ], 200);
    }

    #[Route('/register', methods: ['POST'])]
    public function register(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher,
        PatientRepository $patientRepo
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        $email = $data['email'] ?? null;
        $password = $data['password'] ?? null;
        $firstName = $data['firstName'] ?? null;
        $lastName = $data['lastName'] ?? null;

        if (!$email || !$password) {
            return $this->json(['error' => 'Email y contraseña son requeridos'], 400);
        }

        // Verificar si el paciente ya existe
        $existingPatient = $patientRepo->findOneBy(['email' => $email]);
        if ($existingPatient) {
            return $this->json(['error' => 'El email ya está registrado'], 400);
        }

        // Crear nuevo paciente
        $patient = new Patient();
        $patient->setEmail($email);
        $patient->setFirstName($firstName ?? '');
        $patient->setLastName($lastName ?? '');
        $patient->setNationalId(0);
        $patient->setSocialSecurityNumber('');
        $patient->setPhone('');
        $patient->setAddress('');
        $patient->setBillingData('');
        $patient->setHealthStatus('');
        $patient->setFamilyHistory('');
        $patient->setLifestyleHabits('');
        $patient->setMedicationAllergies('');
        $patient->setRegistrationDate(new \DateTime());

        // Hashear la contraseña
        $hashedPassword = $passwordHasher->hashPassword($patient, $password);
        $patient->setPassword($hashedPassword);

        $em->persist($patient);
        $em->flush();

        // Generar un token simple (en producción usar JWT)
        $token = base64_encode($patient->getEmail() . ':' . time());
        
        // Obtener los roles del paciente
        $roles = $patient->getRoles();
        $role = !empty($roles) ? $roles[0] : 'ROLE_PATIENT';

        return $this->json([
            'token' => $token,
            'role' => $role,
            'id' => $patient->getId(),
            'firstName' => $patient->getFirstName(),
            'lastName' => $patient->getLastName(),
            'email' => $patient->getEmail(),
        ], 201);
    }
}
