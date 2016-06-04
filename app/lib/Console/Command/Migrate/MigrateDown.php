<?php

namespace Honeybee\FrameworkBinding\Silex\Console\Command\Migrate;

use Honeybee\Infrastructure\Migration\MigrationTargetInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class MigrateDown extends MigrateCommand
{
    protected function configure()
    {
        $this
            ->setName('hlx:migrate:down')
            ->setDescription('Migrate down to a specified migration-target version.')
            ->addArgument(
                'target',
                InputArgument::REQUIRED,
                'The name of the migration-target to migrate.'
            )
            ->addOption(
                'to',
                null,
                InputOption::VALUE_REQUIRED,
                "The version to migrate towards."
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->migrate($output, self::DOWN, $input->getArgument('target'), $input->getOption('to') ?: 0);
    }
}
