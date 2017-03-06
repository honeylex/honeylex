<?php

namespace Honeybee\FrameworkBinding\Silex\Console\Command\Migrate;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class MigrateUp extends MigrateCommand
{
    protected function configure()
    {
        $this
            ->setName('migrate:up')
            ->setDescription('Migrate up to a specified migration version.')
            ->addOption(
                'target',
                null,
                InputOption::VALUE_REQUIRED,
                'Name of the target to migrate (if omitted all targets will be run).'
            )
            ->addOption(
                'to',
                null,
                InputOption::VALUE_REQUIRED,
                'The version to migrate towards (if omitted all pendings versions will run).'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->migrate($output, self::UP, $input->getOption('target'), $input->getOption('to'));
    }
}
