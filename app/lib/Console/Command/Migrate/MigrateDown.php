<?php

namespace Honeybee\FrameworkBinding\Silex\Console\Command\Migrate;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class MigrateDown extends MigrateCommand
{
    protected function configure()
    {
        $this
            ->setName('migrate:down')
            ->setDescription('Migrate down to a specified migration version.')
            ->addArgument(
                'target',
                InputArgument::REQUIRED,
                'The name of the target to migrate.'
            )
            ->addOption(
                'to',
                null,
                InputOption::VALUE_REQUIRED,
                'The version to migrate to.'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->migrate($output, self::DOWN, $input->getArgument('target'), $input->getOption('to') ?: 0);
    }
}
