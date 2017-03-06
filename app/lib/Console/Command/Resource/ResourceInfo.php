<?php

namespace Honeybee\FrameworkBinding\Silex\Console\Command\Resource;

use Honeybee\Common\Util\StringToolkit;
use Honeybee\FrameworkBinding\Silex\Config\ConfigProviderInterface;
use Honeybee\Model\Aggregate\AggregateRootTypeMap;
use Honeybee\Projection\ProjectionTypeMap;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

class ResourceInfo extends ResourceCommand
{
    protected $projectionTypeMap;

    protected $aggregateRootTypeMap;

    public function __construct(
        ConfigProviderInterface $configProvider,
        Finder $fileFinder,
        ProjectionTypeMap $projectionTypeMap,
        AggregateRootTypeMap $aggregateRootTypeMap
    ) {
        parent::__construct($configProvider, $fileFinder);
        $this->projectionTypeMap = $projectionTypeMap;
        $this->aggregateRootTypeMap = $aggregateRootTypeMap;
    }

    protected function configure()
    {
        $this
            ->setName('resource:info')
            ->setDescription('Displays details about a resource within a crate.')
            ->addArgument(
                'crate',
                InputArgument::OPTIONAL,
                'The prefix of the crate that contains the target resource.'
            )
            ->addArgument(
                'resource',
                InputArgument::OPTIONAL,
                'The name of the resource to display the details for.'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!$cratePrefix = $input->getArgument('crate')) {
            $cratePrefix = $this->listCrates($input, $output);
        }

        if (!$resourceName = $input->getArgument('resource')) {
            $resourceName = $this->listResources($input, $output, $cratePrefix);
        }

        if (!$resourceName || !$cratePrefix) {
            $output->writeln('<error>You must specify at least a crate and resource.</error>');
            return false;
        }

        $crate = $this->configProvider->getCrateMap()->getItem($cratePrefix);

        $crateDir = $crate->getRootDir();
        $resourcePrefix = $crate->getPrefix().'.'.StringToolkit::asSnakeCase($resourceName);
        $projectionTypes = $this->projectionTypeMap->filterByPrefix($resourcePrefix);
        $aggregateRootType = $this->aggregateRootTypeMap->getItem($resourcePrefix);

        $resourceDirectories = [
            $crateDir.'config/'.$resourceName,
            $crateDir.'lib/'.$resourceName,
            $crateDir.'templates/'.StringToolkit::asSnakeCase($resourceName)
        ];

        $output->writeln('Crate:       '.$crate->getVendor().'/'.$crate->getName());
        $output->writeln('Name:        '.$resourceName);
        $output->writeln('Namespace:   '.$crate->getNamespace().'\\'.$resourceName);
        $output->writeln('Model:       '.$aggregateRootType->getEntityImplementor());
        $output->writeln('Projections: ');
        foreach ($projectionTypes as $projectionType) {
            $output->writeln(sprintf(
                '  %s: %s',
                $projectionType->getVariant(),
                $projectionType->getEntityImplementor()
            ));
        }
        $output->writeln('Directories: ');
        $output->writeln('- '.$crateDir.'/config/'.$resourceName);
        $output->writeln('- '.$crateDir.'/lib/'.$resourceName);
        $output->writeln('- '.$crateDir.'/templates/'.StringToolkit::asSnakeCase($resourceName));
    }
}
