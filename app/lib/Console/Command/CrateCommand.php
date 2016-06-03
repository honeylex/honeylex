<?php

namespace Honeybee\FrameworkBinding\Silex\Console\Command;

use Honeybee\FrameworkBinding\Silex\Config\ConfigProviderInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Yaml;

abstract class CrateCommand extends Command
{
    protected $configProvider;

    public function __construct(ConfigProviderInterface $configProvider)
    {
        $this->configProvider = $configProvider;

        parent::__construct();
    }

    protected function addAutoloadConfig($fqns, $cratePath)
    {
        $composerFile = $this->configProvider->getProjectDir().'/composer.json';
        $composerConfig = json_decode(file_get_contents($composerFile), true);

        if (!isset($composerConfig['autoload']['psr-4'])) {
            $composerConfig['autoload']['psr-4'] = [];
        }
        if (!preg_match('/\\$/', $fqns)) {
            $fqns .= '\\';
        }
        $composerConfig['autoload']['psr-4'][$fqns] = $cratePath;
        (new Filesystem)->dumpFile(
            $composerFile,
            json_encode($composerConfig, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
        );
    }

    protected function removeAutoloadConfig($namespace)
    {
        $composerFile = $this->configProvider->getProjectDir().'/composer.json';
        $composerConfig = json_decode(file_get_contents($composerFile), true);

        if (isset($composerConfig['autoload']['psr-4'])) {
            $autoloads = $composerConfig['autoload']['psr-4'];

            if (isset($autoloads[$namespace])) {
                unset($autoloads[$namespace]);
            }
            $composerConfig['autoload']['psr-4'] = $autoloads;
            (new Filesystem)->dumpFile(
                $composerFile,
                json_encode($composerConfig, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
            );
        }
    }

    protected function updateCratesConfig(array $crates)
    {
        $cratesFile = $this->configProvider->getConfigDir().'/crates.yml';
        (new Filesystem)->dumpFile($cratesFile, sprintf($this->getCratesFileTpl(), Yaml::dump($crates)));
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
