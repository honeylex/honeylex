<?php

namespace Honeylex\Console\Command\Migrate;

use Honeybee\Infrastructure\Migration\MigrationTargetInterface;
use Shrink0r\Monatic\Maybe;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ListTargets extends MigrateCommand
{
    protected function configure()
    {
        $this
            ->setName('migrate:ls')
            ->setDescription('Lists available migration targets.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $migrationTargetMap = $this->migrationService->getMigrationTargetMap();

        if (!count($migrationTargetMap)) {
            $output->writeln('<error>There are no migration targets available.</error>');
            $output->writeln('');
            exit;
        }

        foreach ($migrationTargetMap as $migrationTarget) {
            $this->printMigrationTarget($migrationTarget, $output);
        }
    }

    protected function printMigrationTarget(MigrationTargetInterface $target, OutputInterface $output)
    {
        $pendingCount = $this->migrationService->getPendingMigrations($target->getName())->getSize();
        $latestVersion = Maybe::unit($target->getLatestStructureVersion())->getVersion()->get() ?: 0;
        $output->writeln($target->getName());
        $output->writeln('  Version: '.$latestVersion);
        $output->writeln('  Active: '.($target->isActivated() ? 'true' : 'false'));
        if ($pendingCount === 0) {
            $output->writeln('  Migrations: '.$target->getMigrationList()->getSize());
        } else {
            $output->writeln('  Migrations: '.$target->getMigrationList()->getSize().'/'.$pendingCount.' (pending)');
        }
    }
}
