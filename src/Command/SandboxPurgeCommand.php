<?php

namespace App\Command;

use App\Sandbox\SandboxPurger;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Attribute\Option;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Scheduler\Attribute\AsCronTask;

#[AsCommand(name: 'app:sandbox:purge', description: 'Supprime les données créées par les utilisateurs de l\'API')]
#[AsCronTask('0 3 * * *', timezone: 'Europe/Paris')]
final readonly class SandboxPurgeCommand
{
    public function __construct(private SandboxPurger $purger)
    {
    }

    public function __invoke(
        SymfonyStyle $io,
        #[Option('Ne supprimer que les entrées créées il y a plus de X heures')] ?int $olderThan = null,
    ): int {
        if (null !== $olderThan && $olderThan < 0) {
            $io->error('--older-than doit être positif.');

            return Command::INVALID;
        }

        $deleted = $this->purger->purge($olderThan);
        $io->success(\sprintf('%d entrée(s) supprimée(s).', array_sum($deleted)));

        return Command::SUCCESS;
    }
}
