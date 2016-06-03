<?php

namespace Honeybee\FrameworkBinding\Silex\Console\Command;

use Honeybee\Common\Util\StringToolkit;
use Honeybee\FrameworkBinding\Silex\Config\ConfigProviderInterface;
use Honeybee\FrameworkBinding\Silex\Console\Scafold\SkeletonGenerator;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class MakeCrate extends CrateCommand
{
    protected function configure()
    {
        $this
            ->setName('honeylex:crate:mk')
            ->setDescription('Makes a vanilla crate from a crate-template.')
            ->addOption(
                'description',
                null,
                InputOption::VALUE_REQUIRED,
                "short text describing the crate's purpose"
            )
            ->addOption(
                'location',
                null,
                InputOption::VALUE_IS_ARRAY | InputOption::VALUE_REQUIRED,
                "optional override of the locations that will be searched for skeletons"
            )
            ->addArgument(
                'name',
                InputArgument::REQUIRED,
                'name of the crate'
            )
            ->addArgument(
                'namespace',
                InputArgument::REQUIRED,
                "fully-qualified php namespace of the crate"
            )
            ->addArgument(
                'path',
                null,
                InputArgument::REQUIRED,
                "directory where the crate will be created"
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $name = $input->getArgument('name');
        $fqns = $input->getArgument('namespace');
        $targetPath = $input->getArgument('path');
        if ($name && $targetPath && $fqns) {
            $fqns = trim($fqns, "\\");
            $description = $input->getOption('description');
            $locations = $input->getOption('location') ?: 'not set';
            $prefix = StringToolkit::asSnakecase($name);
            if (!is_array($locations)) {
                $skeletonLocations = [
                    $this->configProvider->getCoreDir() . '/var/skeletons',
                    $this->configProvider->getProjectDir() . '/var/skeletons'
                ];
            } else {
                $skeletonLocations = $locations;
            }
            $skeletonLocations = array_unique($skeletonLocations);

            $output->writeln('Crate name: ' . $name);
            $output->writeln('Crate prefix: ' . $prefix);
            $output->writeln('Crate namespace: ' . $fqns);
            $output->writeln('Crate description: ' . $description);
            $output->writeln('Targetpath: ' . $targetPath);
            $output->writeln('Skeleton locations: ' . implode(', ', $skeletonLocations));

            $data = [
                'crate' => [
                    'name' => $name,
                    'prefix' => $prefix,
                    'namespace' => $fqns,
                    'description' => $description
                ]
            ];
            $skeletonGenerator = new SkeletonGenerator('crate', $skeletonLocations, $targetPath, $data);
            $skeletonGenerator->generate();
            $this->addAutoloadConfig($fqns, $targetPath.'/lib/');

            $crates = [];
            foreach ($this->configProvider->getCrateMap() as $crateToLoad) {
                $crates[] = get_class($crateToLoad);
            }
            $crates[] = $fqns.'\\'.$name.'Crate';
            $this->updateCratesConfig($crates);
        } else {
            $output->writeln('<error>you must specify a crate name</error>');
        }
    }
}
