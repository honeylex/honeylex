<?php

namespace Honeybee\FrameworkBinding\Silex\Console\Command\Crate;

use Honeybee\Common\Util\StringToolkit;
use Honeybee\FrameworkBinding\Silex\Config\ConfigProviderInterface;
use Honeybee\FrameworkBinding\Silex\Console\Scafold\SkeletonGenerator;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

class RemoveCrate extends CrateCommand
{
    protected function configure()
    {
        $this
            ->setName('hlx:crate:rm')
            ->setDescription('Removes a crate from the project. ' . PHP_EOL .
                'Cant be used to remove crates that are loaded from the vendor directory via composer.'
            )
            ->addArgument(
                'crate',
                InputArgument::REQUIRED,
                'The prefix of the crate to remove.'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $prefix = $input->getArgument('crate');
        $crate = $this->configProvider->getCrateMap()->getItem($prefix);
        $crateDir = $crate->getRootDir();
        $appDir = $this->configProvider->getProjectDir().'/app/';
        if (strpos($crateDir, $appDir) === 0) {
            try {
                if ((new Filesystem)->remove($crateDir)) {
                    $output->writeln('<info>Removed crate: '.$crate->getVendor().'/'.$crate->getName().'</info>');
                }
            } catch (\Exception $e) {
                $output->writeln('<error>Failed to remove crate: '.$crate->getVendor().'/'.$crate->getName().'</error>');
            }
            $this->configProvider->getCrateMap()->removeItem($crate);
            $this->removeAutoloadConfig($crate->getNamespace().'\\');
            $output->writeln('<info>Removed crate from composer autoload.</info>');
            $crates = [];
            foreach ($this->configProvider->getCrateMap() as $crateToLoad) {
                $crates[] = get_class($crateToLoad);
            }
            $this->updateCratesConfig($crates);
            $output->writeln('<info>Removed crate from crates.yml config.</info>');
            // have composer dump it's autoloading
            $process = new Process('composer dumpautoload');
            $process->run();
            if (!$process->isSuccessful()) {
                $output->writeln('<error>Failed to dump composer autoloads.</error>');
            } else {
                $output->writeln('<info>'.$process->getOutput().'</info>');
            }
        } else {
            $output->writeln('<error>not allowed to remove crate</error>');
        }
    }
}
