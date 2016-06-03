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
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Yaml;

class RemoveCrate extends Command
{
    const NAME = 'honeylex:crate:rm';

    protected $configProvider;

    public function __construct(ConfigProviderInterface $configProvider)
    {
        $this->configProvider = $configProvider;

        parent::__construct(self::NAME);
    }

    protected function configure()
    {
        $this
            ->setDescription('Removes a crate from the project. ' . PHP_EOL .
                'Cant be used to remove crates that are loaded from the vendor directory via composer.'
            )
            ->addArgument(
                'crate',
                InputArgument::REQUIRED,
                'prefix of the crate to remove'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $prefix = $input->getArgument('crate');
        $crate = $this->configProvider->getCrateMap()->getItem($prefix);
        $crateDir = $crate->getRootDir();
        $appDir = dirname($this->configProvider->getConfigDir());
        if (strpos($crateDir, $appDir) === 0) {
            $output->writeln('<info>removing crate: '.$crate->getName().'</info>');
            (new Filesystem)->remove($crateDir);
            $this->configProvider->getCrateMap()->removeItem($crate);
            $cratesFile = $this->configProvider->getConfigDir().'/crates.yml';
            $cratesToLoad = [];
            foreach ($this->configProvider->getCrateMap() as $crateToLoad) {
                $cratesToLoad[] = get_class($crateToLoad);
            }
            file_put_contents($cratesFile, sprintf($this->getCratesFileTpl(), Yaml::dump($cratesToLoad)));
        } else {
            $output->writeln('<error>not allowed to remove crate</error>');
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
