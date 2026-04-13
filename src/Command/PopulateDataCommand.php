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

        $boxes = [];
        for ($i = 1; $i <= 5; $i++) {
            $box = new Box();
            $box->setName("Box $i");
            $box->setCapacity(1);
            $box->setStatus('Available');
            $this->entityManager->persist($box);
            $boxes[] = $box;
        }

        $treatments = [];
        $treatmentNames = ['Cleaning', 'Filling', 'Root Canal', 'Extraction', 'Whitening', 'Implant', 'Veneer', 'Crown'];
        $treatmentCategories = ['Hygiene', 'Restorative', 'Endodontics', 'Surgery', 'Cosmetic', 'Preventive'];
        foreach ($treatmentNames as $name) {
            $treatment = new Treatment();
            $treatment->setTreatmentName($name);
            $treatment->setDescription($this->randomElement([
                'Professional dental cleaning',
                'Composite dental filling',
                'Endodontic treatment',
                'Tooth extraction',
                'Teeth whitening service',
                'Dental implant placement',
                'Porcelain veneer application',
                'Protective dental crown',
            ]));
            $treatment->setCategory($this->randomElement($treatmentCategories));
            $treatment->setDuration($this->randomElement([30, 45, 60, 90, 120]));
            $treatment->setPrice($this->randomPrice(50, 500));
            $this->entityManager->persist($treatment);
            $treatments[] = $treatment;
        }

        $pathologies = [];
        foreach (['Caries', 'Gingivitis', 'Periodontitis', 'Pulpitis', 'Fracture', 'Abscess', 'Enamel Wear'] as $desc) {
            $pathology = new Pathology();
            $pathology->setDescription($desc);
            $this->entityManager->persist($pathology);
            $pathologies[] = $pathology;
        }

        $teeth = [];
        for ($i = 11; $i <= 48; $i++) {
            if ($i % 10 > 8 || $i % 10 == 0) {
                continue;
            }
            $tooth = new Tooth();
            $tooth->setDescription("Tooth $i");
            $this->entityManager->persist($tooth);
            $teeth[] = $tooth;
        }

        $dentists = [];
        $dentistNames = [
            ['Juan', 'Pérez', 'General Dentistry'],
            ['María', 'García', 'Orthodontics'],
            ['Carlos', 'Rodríguez', 'Endodontics'],
            ['Lucía', 'Martín', 'Prosthodontics'],
            ['Sofía', 'López', 'Pediatric Dentistry'],
        ];
        foreach ($dentistNames as [$first, $last, $specialty]) {
            $dentist = new Dentist();
            $dentist->setFirstName($first);
            $dentist->setLastName($last);
            $dentist->setSpecialty($specialty);
            $dentist->setAvailableDays($this->randomElement(['Mon-Fri', 'Tue-Thu', 'Mon,Wed,Fri', 'Wed-Sat']));
            $dentist->setPhone($this->randomPhone());
            $dentist->setEmail(strtolower("$first.$last@example.com"));
            $dentist->setPassword('password123');
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
        $firstNames = ['Laura', 'Pedro', 'Ana', 'Miguel', 'Elena', 'Adrián', 'Cristina', 'Sergio', 'Marina', 'Pablo'];
        $lastNames = ['Sánchez', 'López', 'Martínez', 'Gómez', 'Fernández', 'Ruiz', 'Domínguez', 'Torres', 'Vargas', 'Navarro'];
        foreach (range(1, 20) as $i) {
            $firstName = $this->randomElement($firstNames);
            $lastName = $this->randomElement($lastNames);
            $patient = new Patient();
            $patient->setFirstName($firstName);
            $patient->setLastName($lastName);
            $patient->setNationalId(10000000 + $i);
            $patient->setSocialSecurityNumber('SS' . str_pad((string) (100 + $i), 3, '0', STR_PAD_LEFT));
            $patient->setPhone($this->randomPhone());
            $patient->setEmail(strtolower("$firstName.$lastName$i@example.com"));
            $patient->setAddress($this->randomElement([
                'Calle Mayor 12',
                'Avenida del Sol 45',
                'Plaza Nueva 8',
                'Calle del Río 23',
                'Camino Real 17',
            ]));
            $patient->setBillingData('Factura ' . $i);
            $patient->setHealthStatus($this->randomElement(['Good', 'Minor issues', 'Needs follow-up']));
            $patient->setFamilyHistory($this->randomElement(['None', 'Diabetes', 'Hypertension']));
            $patient->setLifestyleHabits($this->randomElement(['Healthy', 'Smoker', 'Occasional alcohol']));
            $patient->setMedicationAllergies($this->randomElement(['None', 'Penicillin', 'Aspirin']));
            $patient->setRegistrationDate(new \DateTime('-' . $i . ' days'));
            $patient->setPassword('password123');
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

        foreach ($patients as $index => $patient) {
            $appointment = new Appointment();
            $appointment->setPatient($patient);
            $appointment->setDentist($this->randomElement($dentists));
            $appointment->setBox($this->randomElement($boxes));
            $appointment->setTreatment($this->randomElement($treatments));
            $appointment->setVisitDate(new \DateTime('+' . ($index % 10) . ' days'));
            $appointment->setConsultationReason($this->randomElement([
                'Routine checkup',
                'Tooth pain',
                'Dental cleaning',
                'Follow-up appointment',
            ]));
            $this->entityManager->persist($appointment);

            $odontogram = new Odontogram();
            $odontogram->setPatient($patient);
            $odontogram->setAppointment($appointment);
            $odontogram->setCreationDate(new \DateTime());
            $this->entityManager->persist($odontogram);

            $detail = new OdontogramDetail();
            $detail->setOdontogram($odontogram);
            $detail->setTooth($this->randomElement($teeth));
            $detail->setPathology($this->randomElement($pathologies));
            $detail->setNotes($this->randomElement([
                'Minor issue found',
                'Healthy enamel',
                'Requires follow-up',
            ]));
            $this->entityManager->persist($detail);

            $document = new Document();
            $document->setPatient($patient);
            $document->setType($this->randomElement(['X-Ray', 'Prescription', 'Referral']));
            $document->setFileUrl("https://example.com/docs/patient_{$index}.pdf");
            $document->setCaptureDate(new \DateTime('-' . ($index % 7) . ' days'));
            $this->entityManager->persist($document);
        }

        $this->entityManager->flush();

        $io->success('Database populated successfully with fake data!');

        return Command::SUCCESS;
    }

    private function randomElement(array $items)
    {
        return $items[array_rand($items)];
    }

    private function randomPhone(): string
    {
        return sprintf('6%03d%03d', mt_rand(100, 999), mt_rand(100, 999));
    }

    private function randomPrice(int $min, int $max): string
    {
        $cents = mt_rand(0, 99);
        $amount = mt_rand($min, $max) + ($cents / 100);
        return number_format($amount, 2, '.', '');
    }
}
