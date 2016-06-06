<?php

namespace Honeybee\FrameworkBinding\Silex\Console\Command\Crate;

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
            ->setName('hlx:crate:mk')
            ->setDescription('Makes a vanilla crate from a crate-template.')
            ->addOption(
                'description',
                null,
                InputOption::VALUE_REQUIRED,
                "A short text describing the crate's purpose."
            )
            ->addOption(
                'location',
                null,
                InputOption::VALUE_IS_ARRAY | InputOption::VALUE_REQUIRED,
                "Optional override of the locations that will be searched for (crate)skeletons."
            )
            ->addArgument(
                'name',
                InputArgument::REQUIRED,
                'The name of the crate to make.'
            )
            ->addArgument(
                'namespace',
                InputArgument::REQUIRED,
                "The crates fully-qualified php namespace."
            )
            ->addArgument(
                'path',
                null,
                InputArgument::REQUIRED,
                "The directory path where the crate shall be created."
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $name = $input->getArgument('name');
        $fqns = trim($input->getArgument('namespace'), "\\");
        $targetPath = $input->getArgument('path');
        if ($name && $targetPath && $fqns) {
            $prefix = StringToolkit::asSnakecase($name);
            $description = $input->getOption('description');
            $locations = $input->getOption('location') ?: 'not set';

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
            $output->writeln('Crate dir: ' . $targetPath.'/'.$name);
            $output->writeln('Skeleton locations: ' . implode(', ', $skeletonLocations));

            $data = [
                'timestamp' => date('YmdHis'),
                'crate' => [
                    'name' => $name,
                    'prefix' => $prefix,
                    'namespace' => $fqns,
                    'description' => $description
                ]
            ];
            $skeletonGenerator = new SkeletonGenerator('crate', $skeletonLocations, $targetPath, $data);
            $skeletonGenerator->generate();
            $this->addAutoloadConfig($fqns, $targetPath.'/'.$name.'/lib/');

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
