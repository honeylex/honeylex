<?php

namespace Honeybee\FrameworkBinding\Silex\Config;

interface ConfigProviderInterface
{
    public function getVersion();

    public function getEnvConfigPath();

    public function getCrateMap();

    public function getAppContext();

    public function getAppEnv();

    public function getSetting($setting, $default = null);

    public function hasSetting($setting);

    public function getProjectDir();

    public function getCoreDir();

    public function getConfigDir();

    public function getCoreConfigDir();

    public function getLocalConfigDir();

    public function provide($name);
}
