<?php

namespace Honeybee\FrameworkBinding\Silex\Console\Command\Resource;

use Honeybee\Common\Util\StringToolkit;
use Honeybee\FrameworkBinding\Silex\Config\ConfigProviderInterface;
use Honeybee\Model\Aggregate\AggregateRootTypeMap;
use Honeybee\Projection\ProjectionTypeMap;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ResourceInfo extends ResourceCommand
{
    protected $projectionTypeMap;

    protected $aggregateRootTypeMap;

    public function __construct(
        ConfigProviderInterface $configProvider,
        ProjectionTypeMap $projectionTypeMap,
        AggregateRootTypeMap $aggregateRootTypeMap
    ) {
        parent::__construct($configProvider);

        $this->projectionTypeMap = $projectionTypeMap;
        $this->aggregateRootTypeMap = $aggregateRootTypeMap;
    }

    protected function configure()
    {
        $this
            ->setName('hlx:res:info')
            ->setDescription('Displays detail information about a specific resource from the given crate.')
            ->addArgument(
                'crate',
                InputArgument::REQUIRED,
                "The prefix of the crate that contains the target resource."
            )
            ->addArgument(
                'resource',
                InputArgument::REQUIRED,
                "The name of the resource to display the details for."
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $cratePrefix = $input->getArgument('crate');
        $resourceName = $input->getArgument('resource');

        if (!$resourceName || !$cratePrefix) {
            $output->writeln('<error>You must specify at least a crate-prefix and resource-name.</error>');
            return false;
        }

        $crate = $this->configProvider->getCrateMap()->getItem($cratePrefix);

        $crateDir = $crate->getRootDir();
        $resourcePrefix = $crate->getPrefix().'.'.StringToolkit::asSnakeCase($resourceName);
        $projectionType = $this->projectionTypeMap->getItem($resourcePrefix);
        $aggregateRootType = $this->aggregateRootTypeMap->getItem($resourcePrefix);

        $resourceDirectories = [
            $crateDir.'config/'.$resourceName,
            $crateDir.'lib/'.$resourceName,
            $crateDir.'templates/'.StringToolkit::asSnakeCase($resourceName)
        ];

        $output->writeln('Crate:       ' . $crate->getVendor().'/'.$crate->getName());
        $output->writeln('Name:        ' . $resourceName);
        $output->writeln('Namespace:   ' . $crate->getNamespace().'\\'.$resourceName);
        $output->writeln('Projection:  ' . $projectionType->getEntityImplementor());
        $output->writeln('Model:       ' . $aggregateRootType->getEntityImplementor());
        $output->writeln('Directories: ');
        $output->writeln('- '.$crateDir.'/config/'.$resourceName);
        $output->writeln('- '.$crateDir.'/lib/'.$resourceName);
        $output->writeln('- '.$crateDir.'/templates/'.StringToolkit::asSnakeCase($resourceName));
    }
}
