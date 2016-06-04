<?php

namespace Honeybee\FrameworkBinding\Silex\Config;

use Honeybee\FrameworkBinding\Silex\Crate\CrateMap;
use Honeybee\Infrastructure\Config\ArrayConfig;
use Honeybee\Infrastructure\Config\ConfigInterface;
use Symfony\Component\Finder\Finder;

class ConfigLoader implements ConfigLoaderInterface
{
    protected $appContext;

    protected $appEnv;

    protected $config;

    protected $fileFinder;

    public function __construct($appContext, $appEnv, ConfigInterface $config, Finder $fileFinder)
    {
        $this->appContext = $appContext;
        $this->appEnv = $appEnv;
        $this->config = $config;
        $this->fileFinder = $fileFinder;
    }

    public function getAppContext()
    {
        return $this->appContext;
    }

    public function getAppEnv()
    {
        return $this->appEnv;
    }

    public function getProjectDir()
    {
        return $this->config->get('project.dir');
    }

    public function getCoreDir()
    {
        return $this->config->get('core.dir');
    }

    public function getConfigDir()
    {
        return $this->config->get('project.config_dir');
    }

    public function getCoreConfigDir()
    {
        return $this->config->get('core.config_dir');
    }

    public function loadConfig($name, CrateMap $crateMap = null)
    {
        $config = $this->config->get($name);
        $handlerClass = $config->get('handler');
        $handlerConfig = (array)$config->get('settings', []);
        $handler = new $handlerClass(new ArrayConfig($handlerConfig));
        $configFiles = [
            $this->config->get('core.config_dir') . '/' . $name,
            $this->config->get('project.config_dir') . '/' . $name
        ];

        if ($crateMap) {
            foreach ($crateMap as $prefix => $crate) {
                $finder = clone $this->fileFinder;
                $foundConfigs = $finder->in($crate->getConfigDir())->name($name);
                foreach (iterator_to_array($foundConfigs, true) as $fileInfo) {
                    $configFiles[] = $fileInfo->getPathname();
                }
            }
        }

        return $handler->handle(
            array_values(array_filter(array_unique($configFiles), 'is_readable'))
        );
    }
}
