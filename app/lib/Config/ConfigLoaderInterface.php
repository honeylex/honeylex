<?php

namespace Honeybee\FrameworkBinding\Silex\Config;

use Honeybee\FrameworkBinding\Silex\Crate\CrateMap;

interface ConfigLoaderInterface
{
    public function loadConfig($name, CrateMap $crateMap = null);
}
