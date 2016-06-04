<?php

namespace Honeybee\FrameworkBinding\Silex\Console\Command\Migrate;

use Honeybee\Infrastructure\Migration\MigrationServiceInterface;
use Honeybee\Infrastructure\Migration\MigrationTargetInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ListMigrations extends Command
{
    protected $migrationService;

    public function __construct(MigrationServiceInterface $migrationService)
    {
        $this->migrationService = $migrationService;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('hlx:migrate:ls')
            ->setDescription('Lists available migration targets.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<info>Listing available migration-targets</info>');
        foreach ($this->migrationService->getMigrationTargetMap() as $migrationTarget) {
            $this->printMigrationTarget($migrationTarget, $output);
        }
    }

    protected function printMigrationTarget(MigrationTargetInterface $migrationTarget, OutputInterface $output)
    {
        $output->writeln('- '.$migrationTarget->getName());
        $output->writeln('  version: '.$migrationTarget->getLatestStructureVersion());
        $output->writeln('  active: '.($migrationTarget->isActivated() ? 'true' : 'false'));
    }
}
