<?php

namespace Honeybee\FrameworkBinding\Silex\Console\Command\Migrate;

use Honeybee\Infrastructure\Migration\MigrationList;
use Honeybee\Infrastructure\Migration\MigrationTargetInterface;
use Shrink0r\Monatic\Maybe;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TargetInfo extends MigrateCommand
{
    protected function configure()
    {
        $this
            ->setName('hlx:migrate:info')
            ->setDescription('Displays migration-target details.')
            ->addArgument(
                'target',
                InputArgument::REQUIRED,
                'The prefix of the crate to show the details for.'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $target = $input->getArgument('target');
        $migrationTarget = $this->migrationService->getMigrationTargetMap()->getItem($target);
        if ($migrationTarget) {
            $this->printTargetInfos($migrationTarget, $output);
        } else {
            $output->writeln('<error>Given migration-target is not available.</error>');
        }
    }

    protected function printTargetInfos(MigrationTargetInterface $migrationTarget, OutputInterface $output)
    {
        $latestVersion = Maybe::unit($migrationTarget->getLatestStructureVersion())->getVersion()->get() ?: 0;

        $pending = $this->migrationService->getPendingMigrations($migrationTarget->getName());
        $executed = $this->migrationService->getExecutedMigrations($migrationTarget->getName());

        $output->writeln($migrationTarget->getName());
        $output->writeln('version: '.($latestVersion ?: 0));
        $output->writeln('active: '.($migrationTarget->isActivated() ? 'true' : 'false'));

        $output->writeln('pending migrations:'.($pending->getSize() === 0  ? ' none' : ''));
        foreach ($pending as $pendingMigration) {
            $this->printMigrations($pending, $output);
        }
        $output->writeln('executed migrations:'.($executed->getSize() === 0 ? ' none' : ''));
        foreach ($executed as $executedMigration) {
            $this->printMigrations($executed, $output);
        }
    }

    protected function printMigrations(MigrationList $migrations, OutputInterface $output)
    {
        foreach ($migrations as $migration) {
            $output->writeln('  '.$migration->getName().':');
            $output->writeln('    version: '. $migration->getVersion());
            $output->writeln('    description: '. $migration->getDescription());
        }
    }
}
