<?php

namespace App\Command;

use App\Entity\OdontogramDetail;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:extract-face-from-notes',
    description: 'Extract face information from notes and save to cara column',
)]
class ExtractFaceFromNotesCommand extends Command
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $repository = $this->entityManager->getRepository(OdontogramDetail::class);
        $details = $repository->findAll();

        $updated = 0;
        $skipped = 0;

        $sectionMapping = [
            's1' => 'Superior (Vestibular)',
            's2' => 'Derecha',
            's3' => 'Inferior (Palatino/Lingual)',
            's4' => 'Izquierda',
            's5' => 'Centro (Oclusal)',
        ];

        foreach ($details as $detail) {
            $notes = $detail->getNotes();
            
            if (!$notes) {
                // Si ya tiene cara pero no notas, no hacemos nada
                if ($detail->getCara()) {
                    $skipped++;
                    continue;
                }
                $skipped++;
                continue;
            }

            $cara = $detail->getCara();
            $newNotes = $notes;
            $found = false;

            // 1. Buscar patrón "Sec cara: XYZ" o "Sec face: XYZ"
            if (preg_match('/Sec\s+(?:cara|face)\s*:\s*([^|]+)/i', $notes, $matches)) {
                $cara = trim($matches[1]);
                $newNotes = preg_replace('/\s*\|\s*Sec\s+(?:cara|face)\s*:\s*[^|]+/i', '', $newNotes);
                $newNotes = preg_replace('/^Sec\s+(?:cara|face)\s*:\s*[^|]+\s*\|\s*/i', '', $newNotes);
                $found = true;
            }

            // 2. Buscar patrón "Sec s1:", "Sec s2:", etc.
            foreach ($sectionMapping as $key => $name) {
                if (preg_match('/Sec\s+' . $key . '\s*:\s*/i', $newNotes)) {
                    $cara = $cara ? ($cara . ', ' . $name) : $name;
                    $newNotes = preg_replace('/\s*\|\s*Sec\s+' . $key . '\s*:\s*[^|]+/i', '', $newNotes);
                    $newNotes = preg_replace('/^Sec\s+' . $key . '\s*:\s*[^|]+\s*\|\s*/i', '', $newNotes);
                    $found = true;
                }
            }

            if ($found) {
                $detail->setFace($cara);
                $detail->setNotes(!empty($newNotes) && $newNotes !== $notes ? $newNotes : (empty($newNotes) ? null : $notes));
                $this->entityManager->persist($detail);
                $updated++;
                $io->writeln("Updated tooth ID {$detail->getTooth()->getId()}: extracted cara '{$cara}'");
            } else {
                $skipped++;
            }
        }

        $this->entityManager->flush();

        $io->success("Process completed! Updated: $updated | Skipped: $skipped | Total: " . count($details));

        return Command::SUCCESS;
    }
}
