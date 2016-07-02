<?php

namespace Honeybee\FrameworkBinding\Silex\Console\Command\Crate;

use Honeybee\Common\Util\StringToolkit;
use Honeybee\FrameworkBinding\Silex\Console\Scaffold\SkeletonGenerator;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

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
                'vendor',
                InputArgument::REQUIRED,
                'The vendor that ships this crate.'
            )
            ->addArgument(
                'name',
                InputArgument::REQUIRED,
                "The name of the crate to make"
            )
            ->addArgument(
                'path',
                InputArgument::REQUIRED,
                "The directory path where the crate shall be created."
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $name = $input->getArgument('name');
        $vendor = $input->getArgument('vendor');
        $targetPath = $input->getArgument('path');
        if (!$vendor || !$name || !$targetPath) {
            $output->writeln('<error>You must specify at least a vendor-name, crate-name and target-path.</error>');
            return false;
        }

        $targetPath .= '/'.$name;
        $fqns = sprintf('%s\%s', trim($vendor), trim($name));
        $prefix = StringToolkit::asSnakecase($vendor).'.'.StringToolkit::asSnakecase($name);
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

        $output->writeln('Crate vendor/name: ' . $vendor.'/'.$name);
        $output->writeln('Crate prefix: ' . $prefix);
        $output->writeln('Crate namespace: ' . $fqns);
        $output->writeln('Crate description: ' . $description);
        $output->writeln('Crate dir: ' . $targetPath);
        $output->writeln('Skeleton locations: ' . implode(', ', $skeletonLocations));
        // variables that will be available within skeleton file- and directory-names and within file-contents.
        $data = [
            'timestamp' => date('YmdHis'),
            'crate' => [
                'vendor' => $vendor,
                'name' => $name,
                'prefix' => $prefix,
                'namespace' => $fqns,
                'description' => $description
            ]
        ];
        // generate crate from skeleton and deploy the resulting code to the target-path
        $skeletonGenerator = new SkeletonGenerator('crate', $skeletonLocations, $targetPath, $data);
        $skeletonGenerator->generate();
        // update the composer.json's autoload
        $this->addAutoloadConfig($fqns, $targetPath.'/lib/');
        // update the crates.yml
        $crates = [];
        foreach ($this->configProvider->getCrateMap() as $crateToLoad) {
            $crates[] = get_class($crateToLoad);
        }
        $crates[] = $fqns.'\\'.$name.'Crate';
        $this->updateCratesConfig($crates);
        // have composer dump it's autoloading
        $process = new Process('composer dumpautoload');
        $process->run();
        if (!$process->isSuccessful()) {
            $output->writeln('<error>Failed to dump composer autoloads.</error>');
        } else {
            $output->writeln('<info>'.$process->getOutput().'</info>');
        }
    }
}
