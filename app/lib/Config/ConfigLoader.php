<?php

namespace Honeybee\FrameworkBinding\Silex\Config;

use Honeybee\FrameworkBinding\Silex\Crate\CrateMap;
use Honeybee\Infrastructure\Config\ArrayConfig;
use Honeybee\Infrastructure\Config\ConfigInterface;

class ConfigLoader
{
    protected $config;

    public function __construct(ConfigInterface $config)
    {
        $this->config = $config;
    }

    public function loadConfig($name, CrateMap $crateMap = null)
    {
        $config = $this->config->get($name);
        $handlerClass = $config->get('handler');
        $handlerConfig = (array)$config->get('settings', []);
        $handler = new $handlerClass(new ArrayConfig($handlerConfig));
        $configFiles = [ $this->config->get('core.config_dir') . '/' . $name ];

        if ($crateMap) {
            foreach ($crateMap as $prefix => $crate) {
                $configFile = $crate->getConfigDir() . '/' . $name;
                if (is_readable($configFile)) {
                    $configFiles[] = $configFile;
                }
            }
        }

        return $handler->handle($configFiles);
    }
}
