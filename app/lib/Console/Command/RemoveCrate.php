<?php

namespace Honeybee\FrameworkBinding\Silex\Console\Command;

use Honeybee\Common\Util\StringToolkit;
use Honeybee\FrameworkBinding\Silex\Config\ConfigProviderInterface;
use Honeybee\FrameworkBinding\Silex\Console\Scafold\SkeletonGenerator;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

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
                'prefix of the crate to remove'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $prefix = $input->getArgument('crate');
        $crate = $this->configProvider->getCrateMap()->getItem($prefix);
        $crateDir = $crate->getRootDir();
        $appDir = $this->configProvider->getProjectDir().'/app/';
        if (strpos($crateDir, $appDir) === 0) {
            $output->writeln('<info>removing crate: '.$crate->getName().'</info>');
            (new Filesystem)->remove($crateDir);
            $this->configProvider->getCrateMap()->removeItem($crate);
            $this->removeAutoloadConfig($crate->getNamespace().'\\');

            $crates = [];
            foreach ($this->configProvider->getCrateMap() as $crateToLoad) {
                $crates[] = get_class($crateToLoad);
            }
            $this->updateCratesConfig($crates);
        } else {
            $output->writeln('<error>not allowed to remove crate</error>');
        }
    }
}
