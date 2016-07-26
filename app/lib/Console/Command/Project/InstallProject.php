<?php

namespace Honeybee\FrameworkBinding\Silex\Console\Command\Project;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class InstallProject extends ProjectCommand
{
    protected function configure()
    {
        $this
        ->setName('hlx:project:install')
        ->setDescription('Install and configure a Honeylex project.')
        ->addArgument(
            'name',
            InputArgument::REQUIRED,
            'The application name.'
        )
        ->addArgument(
            'prefix',
            InputArgument::REQUIRED,
            'The application prefix for database.'
        )
        ->addOption(
            'description',
            null,
            InputOption::VALUE_OPTIONAL,
            'The application description.',
            'Honeybee CQRS & ES integration with the Silex framework'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->install(
            $output,
            [
                'project' => [
                    'name' => $input->getArgument('name'),
                    'description' => $input->getOption('description'),
                    'database' => [
                        'prefix' => $input->getArgument('prefix')
                    ]
                ]
            ]
        );
    }
}
