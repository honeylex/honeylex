<?php

namespace Honeybee\FrameworkBinding\Silex\Config;

use Honeybee\FrameworkBinding\Silex\Crate\CrateMap;

class ConfigProvider
{
    public function __construct(ConfigLoader $configLoader, CrateMap $crateMap)
    {
        $this->configLoader = $configLoader;
        $this->crateMap = $crateMap;
    }

    public function provide($config)
    {
        return $this->configLoader->loadConfig($config, $this->crateMap);
    }

    public function getCrateMap()
    {
        return $this->crateMap;
    }
}
