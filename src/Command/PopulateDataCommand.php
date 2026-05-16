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

        // --- Treatments ---
        $treatmentsData = [
            ['Rayos X', 'Radiografia dental'],
            ['Obsturacion', 'Obturación dental (empaste)'],
            ['Endodoncia', 'Tratamiento de conducto'],
            ['Extraccion', 'Extracción dental'],
            ['Blanqueamiento', 'Blanqueamiento dental profesional'],
        ];
        $treatments = [];
        foreach ($treatmentsData as $data) {
            $treatment = new Treatment();
            $treatment->setTreatmentName($data[0]);
            $treatment->setDescription($data[1]);
            $this->entityManager->persist($treatment);
            $treatments[] = $treatment;
        }

        // --- Pathologies ---
        $pathologiesData = ['Caries', 'Gingivitis', 'Periodontitis', 'Fractura', 'Ausencia'];
        $pathologies = [];
        foreach ($pathologiesData as $desc) {
            $pathology = new Pathology();
            $pathology->setDescription($desc);
            $this->entityManager->persist($pathology);
            $pathologies[] = $pathology;
        }

        // --- Statuses ---
        $statuses = [];
        foreach (['Done', 'Pending'] as $name) {
            $status = new Status();
            $status->setName($name);
            $this->entityManager->persist($status);
            $statuses[$name] = $status;
        }

        // --- Boxes ---
        $boxes = [];
        for ($i = 1; $i <= 5; $i++) {
            $box = new Box();
            $box->setName("Box $i");
            $box->setCapacity(1);
            $box->setStatus('available');
            $this->entityManager->persist($box);
            $boxes[] = $box;
        }

        // --- Teeth (FDI notation: permanent 11-48, deciduous 51-85) ---
        $teeth = [];
        foreach ([[11, 48], [51, 85]] as [$from, $to]) {
            for ($i = $from; $i <= $to; $i++) {
                $unit = $i % 10;
                if ($unit === 0 || $unit > 8) continue;
                $tooth = new Tooth();
                $tooth->setDescription((string)$i);
                $this->entityManager->persist($tooth);
                $teeth[(string)$i] = $tooth;
            }
        }

        // --- Dentists ---
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
            $dentist->setPassword($data[6]);
            $this->entityManager->persist($dentist);
            $dentists[] = $dentist;
        }

        // --- Patients ---
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
            $patient->setPassword($data[9]);
            $this->entityManager->persist($patient);
            $patients[] = $patient;
            }
        }

        $this->entityManager->flush();
        $io->text('Base entities created.');

        // --- Appointments ---
        $appointmentsData = [
            [$patients[0], $dentists[0], $boxes[0], $treatments[0], '-5 days', 'completada', 'si', 'Revisión rutinaria'],
            [$patients[0], $dentists[1], $boxes[1], $treatments[1], '+3 days', 'pendiente', 'pendiente', 'Dolor en muela'],
            [$patients[1], $dentists[0], $boxes[2], $treatments[2], '-10 days', 'completada', 'si', 'Seguimiento conducto'],
            [$patients[1], $dentists[2], $boxes[3], $treatments[3], '+7 days', 'pendiente', 'pendiente', 'Extracción muela del juicio'],
            [$patients[2], $dentists[1], $boxes[4], $treatments[4], '-2 days', 'completada', 'si', 'Blanqueamiento dental'],
            [$patients[2], $dentists[0], $boxes[0], $treatments[0], '+14 days', 'pendiente', 'pendiente', 'Limpieza semestral'],
        ];
        $appointments = [];
        foreach ($appointmentsData as $data) {
            $appointment = new Appointment();
            $appointment->setPatient($data[0]);
            $appointment->setDentist($data[1]);
            $appointment->setBox($data[2]);
            $appointment->setTreatment($data[3]);
            $appointment->setVisitDate(new \DateTime($data[4]));
            $appointment->setEstado($data[5]);
            $appointment->setAsistido($data[6]);
            $appointment->setConsultationReason($data[7]);
            $this->entityManager->persist($appointment);
            $appointments[] = $appointment;
        }

        // --- Documents ---
        $documentsData = [
            [$patients[0], 'Radiografía', 'uploads/docs/radiografia_laura_1.jpg', '-30 days'],
            [$patients[0], 'Presupuesto', 'uploads/docs/presupuesto_laura_1.pdf', '-5 days'],
            [$patients[1], 'Radiografía', 'uploads/docs/radiografia_pedro_1.jpg', '-15 days'],
            [$patients[1], 'Informe', 'uploads/docs/informe_pedro_1.pdf', '-10 days'],
            [$patients[2], 'Radiografía', 'uploads/docs/radiografia_ana_1.jpg', '-60 days'],
            [$patients[2], 'Consentimiento', 'uploads/docs/consentimiento_ana_1.pdf', '-2 days'],
        ];
        foreach ($documentsData as $data) {
            $doc = new Document();
            $doc->setPatient($data[0]);
            $doc->setType($data[1]);
            $doc->setFileUrl($data[2]);
            $doc->setCaptureDate(new \DateTime($data[3]));
            $this->entityManager->persist($doc);
        }

        $this->entityManager->flush();
        $io->text('Appointments and documents created.');

        // --- Odontograms (one per completed appointment) ---
        $completedAppointments = [$appointments[0], $appointments[2], $appointments[4]];
        $odontogramPatients   = [$patients[0],      $patients[1],      $patients[2]];
        $odontograms = [];
        foreach ($completedAppointments as $idx => $appt) {
            $odontogram = new Odontogram();
            $odontogram->setPatient($odontogramPatients[$idx]);
            $odontogram->setAppointment($appt);
            $odontogram->setCreationDate(new \DateTime($appt->getVisitDate()->format('Y-m-d')));
            $this->entityManager->persist($odontogram);
            $odontograms[] = $odontogram;
        }

        $this->entityManager->flush();
        $io->text('Odontograms created.');

        // --- Odontogram Details ---
        // Laura - odontogram 0: caries en diente 16 (caras superior e izquierda, tratamiento limpieza, done)
        $detailsData = [
            // [odontogram, toothDesc, pathology, treatment, status, cara, notes]
            [$odontograms[0], '16', $pathologies[0], $treatments[0], $statuses['Done'],    'Superior (Vestibular)',       'Caries tratada con limpieza'],
            [$odontograms[0], '16', $pathologies[0], $treatments[0], $statuses['Done'],    'Izquierda',                  null],
            [$odontograms[0], '21', $pathologies[1], $treatments[0], $statuses['Pending'], 'Centro (Oclusal)',            'Gingivitis incipiente'],
            // Pedro - odontogram 1: endodoncia en diente 36
            [$odontograms[1], '36', $pathologies[2], $treatments[2], $statuses['Done'],    'Superior (Vestibular)',       'Periodontitis avanzada'],
            [$odontograms[1], '36', $pathologies[2], $treatments[2], $statuses['Done'],    'Centro (Oclusal)',            null],
            [$odontograms[1], '46', $pathologies[3], null,           $statuses['Pending'], 'Derecha',                    'Fractura en cara distal'],
            // Ana - odontogram 2: blanqueamiento general
            [$odontograms[2], '11', null,            $treatments[4], $statuses['Done'],    'Superior (Vestibular)',       'Blanqueamiento completado'],
            [$odontograms[2], '21', null,            $treatments[4], $statuses['Done'],    'Superior (Vestibular)',       null],
            [$odontograms[2], '31', null,            $treatments[4], $statuses['Done'],    'Inferior (Palatino/Lingual)', null],
            [$odontograms[2], '41', null,            $treatments[4], $statuses['Done'],    'Inferior (Palatino/Lingual)', null],
        ];
        foreach ($detailsData as $data) {
            $detail = new OdontogramDetail();
            $detail->setOdontogram($data[0]);
            $detail->setTooth($teeth[$data[1]]);
            $detail->setPathology($data[2]);
            $detail->setTreatment($data[3]);
            $detail->setStatus($data[4]);
            $detail->setCara($data[5]);
            $detail->setNotes($data[6]);
            $this->entityManager->persist($detail);
        }

        $this->entityManager->flush();
        $io->success('All tables populated successfully!');

        return Command::SUCCESS;
    }

    private function cleanupDuplicates(SymfonyStyle $io): void
    {
        $io->section('Removing duplicate and obsolete entries safely...');
        $connection = $this->entityManager->getConnection();

        $connection->executeStatement('SET FOREIGN_KEY_CHECKS = 0');

        $tables = ['appointment', 'odontogram_detail', 'odontogram', 'document', 'patient', 'dentist', 'box', 'treatment', 'pathology', 'tooth', 'status'];
        $platform = $connection->getDatabasePlatform();
        foreach ($tables as $table) {
            $connection->executeStatement($platform->getTruncateTableSQL($table));
        }

        $connection->executeStatement('SET FOREIGN_KEY_CHECKS = 1');

        $io->text('Cleanup complete.');
    }
}
