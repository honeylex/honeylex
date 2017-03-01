<?php

namespace Honeybee\FrameworkBinding\Silex\Console\Command\Project;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class ConfigureProject extends ProjectCommand
{
    protected function configure()
    {
        $this
            ->setName('hlx:project:configure')
            ->setDescription('Configure a Honeylex project.')
            ->addArgument(
                'name',
                InputArgument::OPTIONAL,
                'The application name.'
            )
            ->addArgument(
                'prefix',
                InputArgument::OPTIONAL,
                'The application prefix.'
            )
            ->addArgument(
                'locale',
                InputArgument::OPTIONAL,
                'The application locale.'
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
        $output->writeln('Honeylex projection configuration');
        $output->writeln('---------------------------------');
        $output->writeln('');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $helper = $this->getHelper('question');

        $this->writeHeader($output);

        if (!$projectName = $input->getArgument('name')) {
            $currentProjectName = $this->configProvider->getSetting('project.name');
            $question = new Question("What is your application name? [$currentProjectName]: ", $currentProjectName);
            $projectName = $helper->ask($input, $output, $question);
        }

        if (!$projectName || !preg_match('#^[a-z0-9]#i', $projectName)) {
            $output->writeln('<error>You must specify a valid application name.</error>');
            return false;
        }

        if (!$projectPrefix = $input->getArgument('prefix')) {
            $currentProjectPrefix = $this->configProvider->getSetting('project.app.prefix');
            $question = new Question(
                "What is your project prefix? (format:[a-z0-9_-]) [$currentProjectPrefix]: ",
                $currentProjectPrefix
            );
            $projectPrefix = $helper->ask($input, $output, $question);
        }

        if (!$projectPrefix || !preg_match('#^[a-z0-9][a-z0-9_-]+[a-z0-9]$#', $projectPrefix)) {
            $output->writeln('<error>You must specify a valid project prefix.</error>');
            return false;
        }

        if (!$projectLocale = $input->getArgument('locale')) {
            $currentProjectLocale = $this->configProvider->getSetting('project.translation.default_locale');
            $question = new Question("What is your project locale? [$currentProjectLocale]: ", $currentProjectLocale);
            $projectLocale = $helper->ask($input, $output, $question);
        }

        if (!$projectLocale) {
            $output->writeln('<error>You must specify a default project locale.</error>');
            return false;
        }

        if (!$projectDescription = $input->getOption('description')) {
            $projectDescription = $this->configProvider->getSetting('project.description');
        }

        $this->configure(
            $output,
            [
                'project' => [
                    'name' => $projectName,
                    'description' => $projectDescription,
                    'app' => [
                        'prefix' => $projectPrefix
                    ],
                    'database' => [
                        'prefix' => $projectPrefix
                    ],
                    'email' => [
                        'from_email' => "$projectPrefix@honeylex.dev",
                        'from_name' => $projectName
                    ],
                    'translation' => [
                        'default_locale' => $projectLocale
                    ]
                ]
            ]
        );
    }
}
