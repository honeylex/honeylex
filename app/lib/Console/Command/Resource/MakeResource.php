<?php

namespace Honeybee\FrameworkBinding\Silex\Console\Command\Resource;

use Honeybee\Common\Util\StringToolkit;
use Honeybee\FrameworkBinding\Silex\Console\Scaffold\SkeletonGenerator;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class MakeResource extends ResourceCommand
{
    protected function configure()
    {
        $this
            ->setName('resource:mk')
            ->setDescription('Makes a resource from a template.')
            ->addArgument(
                'crate',
                InputArgument::OPTIONAL,
                'The prefix of the crate to make the resource in.'
            )
            ->addArgument(
                'resource',
                InputArgument::OPTIONAL,
                'The name of the resource to make.'
            )
            ->addOption(
                'description',
                null,
                InputOption::VALUE_REQUIRED,
                'A short text describing the resource\'s purpose.'
            )
            ->addOption(
                'location',
                null,
                InputOption::VALUE_IS_ARRAY | InputOption::VALUE_REQUIRED,
                'Optional override of the locations that will be searched for (resource)skeletons.'
            );
    }

    protected function writeHeader(OutputInterface $output)
    {
        $output->writeln('');
        $output->writeln('Honeylex resource scaffolding');
        $output->writeln('-----------------------------');
        $output->writeln('');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $helper = $this->getHelper('question');

        if (!$cratePrefix = $input->getArgument('crate')) {
            $this->writeHeader($output);
            $cratePrefix = $this->listCrates($input, $output);
        }

        if (!$resourceName = $input->getArgument('resource')) {
            $question = new Question('Please provide a resource name: ');
            $resourceName = $helper->ask($input, $output, $question);
        }

        if (!$resourceName || !$cratePrefix) {
            $output->writeln('<error>You must specify at least a crate and resource.</error>');
            return false;
        }

        $crate = $this->configProvider->getCrateMap()->getItem($cratePrefix);

        $resourcePrefix = $cratePrefix.'.'.StringToolkit::asSnakeCase($resourceName);
        $crateDir = $crate->getRootDir();
        $description = $input->getOption('description');
        $locations = $input->getOption('location') ?: null;

        if (!is_array($locations)) {
            $skeletonLocations = [];
            $projectSkeletonDir = $this->configProvider->getProjectDir().'/var/skeletons';
            if (is_readable($projectSkeletonDir)) {
                $skeletonLocations[] = $projectSkeletonDir;
            }
            $coreSkeletonDir = $this->configProvider->getCoreDir().'/var/skeletons';
            if (is_readable($coreSkeletonDir)) {
                $skeletonLocations[] = $coreSkeletonDir;
            }
        } else {
            $skeletonLocations = $locations;
        }
        $skeletonLocations = array_unique($skeletonLocations);

        $output->writeln('Target crate: '.$crate->getVendor().'/'.$crate->getName());
        $output->writeln('Resource prefix: '.$resourcePrefix);
        $output->writeln('Resource namespace: '.$crate->getNamespace().'\\'.$resourceName);
        $output->writeln('Resource description: '.$description);
        $output->writeln('Directories: ');
        $output->writeln('  - '.$crate->getRootDir().'/config/'.$resourceName);
        $output->writeln('  - '.$crate->getRootDir().'/lib/'.$resourceName);
        $output->writeln('  - '.$crate->getRootDir().'/templates/'.StringToolkit::asSnakeCase($resourceName));
        $output->writeln('Skeleton locations:');
        foreach ($skeletonLocations as $skeletonLocation) {
            $output->writeln('  - '.$skeletonLocation);
        }

        $data = [
            'timestamp' => date('YmdHis'),
            'crate' => [
                'vendor' => $crate->getVendor(),
                'name' => $crate->getName(),
                'prefix' => $crate->getPrefix(),
                'namespace' => $crate->getNamespace(),
                'description' => $crate->getDescription()
            ],
            'resource' => [
                'name' => $resourceName,
                'variant' => 'Standard',
                'prefix' => $resourcePrefix,
                'description' => $description
            ]
        ];

        $skeletonGenerator = new SkeletonGenerator(
            $this->configProvider,
            'resource',
            $skeletonLocations,
            $crate->getRootDir(),
            $data
        );
        $skeletonGenerator->generate();
        // @todo auto run migrations?
    }
}
