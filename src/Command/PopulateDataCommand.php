<?php

namespace App\Command;

use App\Entity\Appointment;
use App\Entity\Box;
use App\Entity\Dentist;
use App\Entity\Patient;
use App\Entity\Treatment;
use App\Entity\Pathology;
use App\Entity\Status;
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
        $io->title('Cleaning up duplicates and populating database...');

        $this->cleanupDuplicates($io);

        $treatmentsData = [
            ['Limpieza', 'Limpieza dental profunda'],
            ['Obsturacion', 'Obturación dental (empaste)'],
            ['Endodoncia', 'Tratamiento de conducto'],
            ['Extraccion', 'Extracción dental'],
            ['Blanqueamiento', 'Blanqueamiento dental profesional'],
        ];

        $pathologiesData = [
            'Caries',
            'Gingivitis',
            'Periodontitis',
            'Fractura',
            'Ausencia',
        ];

        $boxes = [];
        for ($i = 1; $i <= 5; $i++) {
            $name = "Box $i";
            $box = $this->entityManager->getRepository(Box::class)->findOneBy(['name' => $name]);
            if (!$box) {
                $box = new Box();
                $box->setName($name);
                $box->setCapacity(1);
                $this->entityManager->persist($box);
            }
            $boxes[] = $box;
        }

        $treatments = [];
        foreach ($treatmentsData as $data) {
            $treatment = $this->entityManager->getRepository(Treatment::class)->findOneBy(['treatmentName' => $data[0]]);
            if (!$treatment) {
                $treatment = new Treatment();
                $treatment->setTreatmentName($data[0]);
                $treatment->setDescription($data[1]);
                $this->entityManager->persist($treatment);
            }
            $treatments[] = $treatment;
        }

        $pathologies = [];
        foreach ($pathologiesData as $desc) {
            $pathology = $this->entityManager->getRepository(Pathology::class)->findOneBy(['description' => $desc]);
            if (!$pathology) {
                $pathology = new Pathology();
                $pathology->setDescription($desc);
                $this->entityManager->persist($pathology);
            }
            $pathologies[] = $pathology;
        }

        // 4. Statuses (Restricted to Done, Pending, Absent)
        $statusesData = ['Done', 'Pending', 'Absent'];
        foreach ($statusesData as $desc) {
            $status = $this->entityManager->getRepository(Status::class)->findOneBy(['name' => $desc]);
            if (!$status) {
                $status = new Status();
                $status->setName($desc);
                $this->entityManager->persist($status);
            }
        }

        // 5. Teeth
        $teeth = [];
        for ($i = 11; $i <= 48; $i++) {
            if ($i % 10 > 8 || $i % 10 == 0) continue; 
            $desc = "Tooth $i";
            $tooth = $this->entityManager->getRepository(Tooth::class)->findOneBy(['description' => $desc]);
            if (!$tooth) {
                $tooth = new Tooth();
                $tooth->setDescription($desc);
                $this->entityManager->persist($tooth);
            }
            $teeth[] = $tooth;
        }

        // 6. Dentists (User Data)
        $dentistsData = [
            ['Juan', 'Pérez', 'General Dentistry', 'Mon-Fri', '555-0101', 'juan.perez@example.com', 'password123'],
            ['María', 'García', 'Orthodontics', 'Tue-Thu', '555-0102', 'maria.garcia@example.com', 'password123'],
            ['Carlos', 'Rodríguez', 'Endodontics', 'Mon,Wed,Fri', '555-0103', 'carlos.rod@example.com', 'password123'],
        ];
        $dentists = [];
        foreach ($dentistsData as $data) {
            $dentist = $this->entityManager->getRepository(Dentist::class)->findOneBy(['email' => $data[5]]);
            if (!$dentist) {
                $dentist = new Dentist();
                $dentist->setFirstName($data[0]);
                $dentist->setLastName($data[1]);
                $dentist->setSpecialty($data[2]);
                $dentist->setAvailableDays($data[3]);
                $dentist->setPhone($data[4]);
                $dentist->setEmail($data[5]);
                $dentist->setPassword($this->passwordHasher->hashPassword($dentist, $data[6]));
                $this->entityManager->persist($dentist);
            }
            $dentists[] = $dentist;
        }

        // 7. Patients (User Data)
        $patientsData = [
            ['Laura', 'Sánchez', 12345678, 'SS123', '600000001', 'laura@example.com', 8, 'Calle A, 1', 'Bill 1', 'password123'],
            ['Pedro', 'López', 87654321, 'SS456', '600000002', 'pedro@example.com', 12, 'Calle B, 2', 'Bill 2', 'password123'],
            ['Ana', 'Martínez', 23456781, 'SS789', '600000003', 'ana@example.com', 34, 'Calle C, 3', 'Bill 3', 'password123'],
        ];
        $patients = [];
        foreach ($patientsData as $data) {
            $patient = $this->entityManager->getRepository(Patient::class)->findOneBy(['email' => $data[5]]);
            if (!$patient) {
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
                $patient->setRegistrationDate(new \DateTime());
                $patient->setHealthStatus('Good');
                $patient->setFamilyHistory('None');
                $patient->setLifestyleHabits('None');
                $patient->setMedicationAllergies('None');
                $this->entityManager->persist($patient);
            }
            $patients[] = $patient;
        }

        $this->entityManager->flush();
        $io->success('Database cleaned and populated successfully!');

        return Command::SUCCESS;
    }

    private function cleanupDuplicates(SymfonyStyle $io): void
    {
        $io->section('Removing duplicate and obsolete entries safely...');
        $connection = $this->entityManager->getConnection();

        $connection->executeStatement('SET FOREIGN_KEY_CHECKS = 0');

        // Cleanup Dentist and Patient duplicates
        $connection->executeStatement('DELETE d1 FROM dentist d1 INNER JOIN dentist d2 WHERE d1.id > d2.id AND d1.email = d2.email');
        $connection->executeStatement('DELETE p1 FROM patient p1 INNER JOIN patient p2 WHERE p1.id > p2.id AND p1.email = p2.email');
        
        // Cleanup Pathologies and Treatments duplicates
        $connection->executeStatement('DELETE p1 FROM pathology p1 INNER JOIN pathology p2 WHERE p1.id > p2.id AND p1.description = p2.description');
        $connection->executeStatement('DELETE t1 FROM treatment t1 INNER JOIN treatment t2 WHERE t1.id > t2.id AND t1.treatment_name = t2.treatment_name');
        
        // Cleanup Status duplicates and obsolete
        $connection->executeStatement('DELETE s1 FROM status s1 INNER JOIN status s2 WHERE s1.id > s2.id AND s1.name = s2.name');
        $connection->executeStatement("DELETE FROM status WHERE name NOT IN ('Done', 'Pending', 'Absent')");

        $connection->executeStatement('SET FOREIGN_KEY_CHECKS = 1');

        $io->text('Cleanup complete.');
    }
}
