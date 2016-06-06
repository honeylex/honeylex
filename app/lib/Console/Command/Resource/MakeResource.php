<?php

namespace Honeybee\FrameworkBinding\Silex\Console\Command\Resource;

use Honeybee\Common\Util\StringToolkit;
use Honeybee\FrameworkBinding\Silex\Console\Scafold\SkeletonGenerator;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class MakeResource extends ResourceCommand
{
    protected function configure()
    {
        $this
            ->setName('hlx:res:mk')
            ->setDescription('Makes a vanilla resource from a resource-template.')
            ->addOption(
                'description',
                null,
                InputOption::VALUE_REQUIRED,
                "A short text describing the resource's purpose."
            )
            ->addOption(
                'location',
                null,
                InputOption::VALUE_IS_ARRAY | InputOption::VALUE_REQUIRED,
                "Optional override of the locations that will be searched for (resource)skeletons."
            )
            ->addArgument(
                'crate',
                InputArgument::REQUIRED,
                "The prefix of the crate to make the resource in."
            )
            ->addArgument(
                'name',
                null,
                InputArgument::REQUIRED,
                "The name of the resource to make."
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $cratePrefix = $input->getArgument('crate');
        $resourceName = $input->getArgument('name');
        $crate = $this->configProvider->getCrateMap()->getItem($cratePrefix);
        if (!$resourceName || !$cratePrefix || !$crate) {
            $output->writeln('<error>You must specify at least a crate-prefix and resource-name.</error>');
            return false;
        }

        $resourcePrefix = $cratePrefix.'.'.StringToolkit::asSnakeCase($resourceName);
        $crateDir = $crate->getRootDir();
        $description = $input->getOption('description');
        $locations = $input->getOption('location') ?: null;

        if (!is_array($locations)) {
            $skeletonLocations = [
                $this->configProvider->getCoreDir() . '/var/skeletons',
                $this->configProvider->getProjectDir() . '/var/skeletons'
            ];
        } else {
            $skeletonLocations = $locations;
        }
        $skeletonLocations = array_unique($skeletonLocations);

        $output->writeln('Target crate: ' . $crate->getVendor().'/'.$crate->getName());
        $output->writeln('Name/Prefix: ' . $resourceName.'/'.$resourcePrefix);
        $output->writeln('Namespace: ' . $crate->getNamespace().'\\'.$resourceName);
        $output->writeln('Description: ' . $description);
        $output->writeln('Directories: ');
        $output->writeln('- '.$crate->getRootDir().'/config/'.$resourceName);
        $output->writeln('- '.$crate->getRootDir().'/lib/'.$resourceName);
        $output->writeln('- '.$crate->getRootDir().'/templates/'.StringToolkit::asSnakeCase($resourceName));
        $output->writeln('Skeleton-locations:');
        foreach ($skeletonLocations as $skeletonLocation) {
            $output->writeln('- '. $skeletonLocation);
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
                'prefix' => $resourcePrefix,
                'description' => $description
            ]
        ];

        $skeletonGenerator = new SkeletonGenerator('resource', $skeletonLocations, $crate->getRootDir(), $data);
        $skeletonGenerator->generate();
    }
}