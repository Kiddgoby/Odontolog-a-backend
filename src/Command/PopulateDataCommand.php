<?php

namespace App\Command;

use App\Entity\Appointment;
use App\Entity\Box;
use App\Entity\Dentist;
use App\Entity\Patient;
use App\Entity\Treatment;
use App\Entity\Pathology;
use App\Entity\Tooth;
use App\Entity\Odontogram;
use App\Entity\OdontogramDetail;
use App\Entity\Document;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:populate-data',
    description: 'Populates the database with sample data for all tables.',
)]
class PopulateDataCommand extends Command
{
    private EntityManagerInterface $entityManager;
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->passwordHasher = $passwordHasher;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Populating database with sample data...');

        // 1. Boxes
        $boxes = [];
        for ($i = 1; $i <= 5; $i++) {
            $box = new Box();
            $box->setName("Box $i");
            $box->setCapacity(1);
            $box->setStatus('Available');
            $this->entityManager->persist($box);
            $boxes[] = $box;
        }

        // 2. Treatments
        $treatmentsData = [
            ['Cleaning', 'Professional dental cleaning'],
            ['Filling', 'Composite dental filling'],
            ['Root Canal', 'Endodontic treatment'],
            ['Extraction', 'Tooth extraction'],
            ['Whitening', 'Teeth whitening service'],
        ];
        $treatments = [];
        foreach ($treatmentsData as $data) {
            $treatment = new Treatment();
            $treatment->setTreatmentName($data[0]);
            $treatment->setDescription($data[1]);
            $this->entityManager->persist($treatment);
            $treatments[] = $treatment;
        }

        // 3. Pathologies
        $pathologiesData = ['Caries', 'Gingivitis', 'Periodontitis', 'Pulpitis', 'Fracture'];
        $pathologies = [];
        foreach ($pathologiesData as $desc) {
            $pathology = new Pathology();
            $pathology->setDescription($desc);
            $this->entityManager->persist($pathology);
            $pathologies[] = $pathology;
        }

        // 4. Teeth
        $teeth = [];
        for ($i = 11; $i <= 48; $i++) {
            // Simple validation for tooth numbers (FDI system skips some)
            if ($i % 10 > 8 || $i % 10 == 0) continue; 
            $tooth = new Tooth();
            $tooth->setDescription("Tooth $i");
            $this->entityManager->persist($tooth);
            $teeth[] = $tooth;
        }

        // 5. Dentists
        $dentistsData = [
            ['Juan', 'Pérez', 'General Dentistry', 'Mon-Fri', '555-0101', 'juan.perez@example.com', 'password123'],
            ['María', 'García', 'Orthodontics', 'Tue-Thu', '555-0102', 'maria.garcia@example.com', 'password123'],
            ['Carlos', 'Rodríguez', 'Endodontics', 'Mon,Wed,Fri', '555-0103', 'carlos.rod@example.com', 'password123'],
        ];
        $dentists = [];
        foreach ($dentistsData as $data) {
            $dentist = new Dentist();
            $dentist->setFirstName($data[0]);
            $dentist->setLastName($data[1]);
            $dentist->setSpecialty($data[2]);
            $dentist->setAvailableDays($data[3]);
            $dentist->setPhone($data[4]);
            $dentist->setEmail($data[5]);
            $dentist->setPassword($data[6]); // Texto plano
            $this->entityManager->persist($dentist);
            $dentists[] = $dentist;
        }

        // 6. Patients
        $patientsData = [
            ['Laura', 'Sánchez', 12345678, 'SS123', '600000001', 'laura@example.com', 8, 'Calle A, 1', 'Bill 1', 'password123'],
            ['Pedro', 'López', 87654321, 'SS456', '600000002', 'pedro@example.com', 12, 'Calle B, 2', 'Bill 2', 'password123'],
            ['Ana', 'Martínez', 23456781, 'SS789', '600000003', 'ana@example.com', 34, 'Calle C, 3', 'Bill 3', 'password123'],
        ];
        $patients = [];
        foreach ($patientsData as $data) {
            $patient = new Patient();
            $patient->setFirstName($data[0]);
            $patient->setLastName($data[1]);
            $patient->setNationalId($data[2]);
            $patient->setSocialSecurityNumber($data[3]);
            $patient->setPhone($data[4]);
            $patient->setEmail($data[5]);
            $patient->setAge($data[6]);
            $patient->setAddress($data[7]);
            $patient->setBillingData($data[8]);
            $patient->setHealthStatus('Good');
            $patient->setFamilyHistory('None');
            $patient->setLifestyleHabits('Healthy');
            $patient->setMedicationAllergies('None');
            $patient->setRegistrationDate(new \DateTime());
            
            // Asignar contraseña en texto plano
            $patient->setPassword($data[9]);
            
            $this->entityManager->persist($patient);
            $patients[] = $patient;
        }

        $this->entityManager->flush();

        // 7. Appointments & Related
        foreach ($patients as $index => $patient) {
            $appointment = new Appointment();
            $appointment->setPatient($patient);
            $appointment->setDentist($dentists[$index % count($dentists)]);
            $appointment->setBox($boxes[$index % count($boxes)]);
            $appointment->setTreatment($treatments[$index % count($treatments)]);
            $appointment->setVisitDate(new \DateTime("+".($index+1)." days"));
            $appointment->setConsultationReason('Routine checkup');
            $this->entityManager->persist($appointment);

            // Odontogram
            $odontogram = new Odontogram();
            $odontogram->setPatient($patient);
            $odontogram->setAppointment($appointment);
            $odontogram->setCreationDate(new \DateTime());
            $this->entityManager->persist($odontogram);

            // Odontogram Detail
            $detail = new OdontogramDetail();
            $detail->setOdontogram($odontogram);
            $detail->setTooth($teeth[array_rand($teeth)]);
            $detail->setPathology($pathologies[array_rand($pathologies)]);
            $detail->setNotes('Minor issue found');
            $this->entityManager->persist($detail);

            // Document
            $document = new Document();
            $document->setPatient($patient);
            $document->setType('X-Ray');
            $document->setFileUrl("https://example.com/docs/patient_$index.pdf");
            $document->setCaptureDate(new \DateTime());
            $this->entityManager->persist($document);
        }

        $this->entityManager->flush();

        $io->success('Database populated successfully!');

        return Command::SUCCESS;
    }
}
