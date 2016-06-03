<?php

namespace Honeybee\FrameworkBinding\Silex\Console\Command;

use Honeybee\Common\Util\StringToolkit;
use Honeybee\FrameworkBinding\Silex\Config\ConfigProviderInterface;
use Honeybee\FrameworkBinding\Silex\Console\Scafold\SkeletonGenerator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

class MakeCrate extends Command
{
    const NAME = 'honeylex:crate:mk';

    protected $configProvider;

    public function __construct(ConfigProviderInterface $configProvider)
    {
        $this->configProvider = $configProvider;

        parent::__construct(self::NAME);
    }

    protected function configure()
    {
        $this
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
                    dirname(dirname($this->configProvider->getCoreConfigDir())) . '/var/skeletons',
                    dirname(dirname($this->configProvider->getConfigDir())) . '/var/skeletons'
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

            $cratesFile = $this->configProvider->getConfigDir().'/crates.yml';
            $cratesToLoad = [];
            foreach ($this->configProvider->getCrateMap() as $crateToLoad) {
                $cratesToLoad[] = get_class($crateToLoad);
            }
            $cratesToLoad[] = $fqns.'\\'.$name.'Crate';
            file_put_contents($cratesFile, sprintf($this->getCratesFileTpl(), Yaml::dump($cratesToLoad)));
        } else {
            $output->writeln('<error>you must specify a crate name</error>');
        }
    }

    protected function getCratesFileTpl()
    {
        return <<<CRATES
#
# list of crates that will be loaded into the app.
---
%s
CRATES;
    }
}
