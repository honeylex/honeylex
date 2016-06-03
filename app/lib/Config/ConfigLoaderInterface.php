<?php

namespace Honeybee\FrameworkBinding\Silex\Config;

use Honeybee\FrameworkBinding\Silex\Crate\CrateMap;

interface ConfigLoaderInterface
{
    public function getAppContext();

    public function getAppEnv();

    public function getProjectDir();

    public function getCoreDir();

    public function getConfigDir();

    public function getCoreConfigDir();

    public function loadConfig($name, CrateMap $crateMap = null);
}
