<?php

namespace Honeybee\FrameworkBinding\Silex\Console\Command\Project;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class InstallProject extends ProjectCommand
{
    protected function configure()
    {
        $this
            ->setName('hlx:project:install')
            ->setDescription('Install and configure a Honeylex project.')
            ->addArgument(
                'name',
                InputArgument::OPTIONAL,
                'The application name.'
            )
            ->addOption(
                'description',
                null,
                InputOption::VALUE_OPTIONAL,
                'The application description.',
                'Honeybee CQRS & ES integration with the Silex framework'
            );
    }

    protected function writeHeader(OutputInterface $output)
    {
        $output->writeln('');
        $output->writeln('Honeylex projection installation');
        $output->writeln('--------------------------------');
        $output->writeln('');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $helper = $this->getHelper('question');

        if (!$name = $input->getArgument('name')) {
            $this->writeHeader($output);
            $question = new Question('What is your application name? [Honeylex]: ', 'Honeylex');
            $name = $helper->ask($input, $output, $question);
        }

        if (!$name) {
            $output->writeln('<error>You must specify an application name.</error>');
            return false;
        }

        $lcName = strtolower($name);
        $this->install(
            $output,
            [
                'project' => [
                    'name' => $name,
                    'description' => $input->getOption('description'),
                    'app' => [
                        'prefix' => $lcName
                    ],
                    'database' => [
                        'prefix' => $lcName
                    ],
                    'email' => [
                        'from_email' => sprintf('%1$s@%1$s.dev', $lcName),
                        'from_name' => $name
                    ],
                    'translation' => [
                        'default_locale' => 'en',
                        'locale_fallbacks' => [ 'en', 'de' ]
                    ]
                ]
            ]
        );
    }
}
