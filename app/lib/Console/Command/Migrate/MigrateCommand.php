<?php

namespace Honeybee\FrameworkBinding\Silex\Console\Command\Migrate;

use Honeybee\FrameworkBinding\Silex\Console\Command\Command;
use Honeybee\Infrastructure\Migration\MigrationServiceInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Honeybee\FrameworkBinding\Silex\Config\ConfigProviderInterface;

abstract class MigrateCommand extends Command
{
    const ALL = 'all';

    const UP = 'up';

    const DOWN = 'down';

    protected $migrationService;

    public function __construct(
        ConfigProviderInterface $configProvider,
        MigrationServiceInterface $migrationService
    ) {
        parent::__construct($configProvider);

        $this->migrationService = $migrationService;
    }

    protected function migrate(OutputInterface $output, $direction, $target = null, $toVersion = null)
    {
        $migrationTargetMap = $this->migrationService->getMigrationTargetMap();

        if (!count($migrationTargetMap)) {
            $output->writeln('<error>There are no migration targets available.</error>');
            $output->writeln('');
            return;
        }

        if ($target && $target !== self::ALL) {
            if (!$migrationTargetMap->hasKey($target)) {
                $output->writeln('<error>The given migration target does not exist.</error>');
                $output->writeln('');
                return;
            }
            $migrationTarget = $migrationTargetMap->getItem($target);
        } else {
            $migrationTarget = null;
            if ($toVersion !== null) {
                $output->writeln('<error>Version parameter only supported together with a valid target.</error>');
                return;
            }
        }
        if ($toVersion !== null) {
            $latestVersion = $migrationTarget->getLatestStructureVersion()->getVersion();
            if ($direction === self::UP && (int)$latestVersion >= (int)$toVersion) {
                $output->writeln(
                    '<error>The version to migrate to must not be smaller than the current head version.</error>'
                );
                return;
            } elseif ($direction === self::DOWN && (int)$latestVersion <= (int)$toVersion) {
                $output->writeln(
                    '<error>The version to migrate to must not be greater than the current head version.</error>'
                );
                return;
            }
        }

        if (!$migrationTarget) {
            foreach ($migrationTargetMap as $targetName => $migrationTarget) {
                if ($migrationTarget->isActivated()) {
                    $output->writeln('Running migrations for "'.$targetName.'"');
                    foreach ($this->migrationService->migrate($targetName) as $runMigration) {
                        $output->writeln('  Executed migration "'.$runMigration->getName().'"');
                    }
                }
            }
        } else {
            foreach ($this->migrationService->migrate($migrationTarget->getName(), $toVersion) as $runMigration) {
                $output->writeln('  Executed migration "'.$runMigration->getName().'"');
            }
        }
    }
}
