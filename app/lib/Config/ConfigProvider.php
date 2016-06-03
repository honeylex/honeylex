<?php

namespace Honeybee\FrameworkBinding\Silex\Config;

use Honeybee\Common\Error\RuntimeError;
use Honeybee\FrameworkBinding\Silex\Crate\CrateMap;

class ConfigProvider implements ConfigProviderInterface
{
    private $version;

    public function __construct(ConfigLoaderInterface $configLoader, CrateMap $crateMap)
    {
        $this->configLoader = $configLoader;
        $this->crateMap = $crateMap;
    }

    public function getVersion()
    {
        if (!$this->version) {
            $versionFile = $this->getCoreDir().'/VERSION.txt';
            $this->version = trim(file_get_contents($versionFile));
        }
        return $this->version;
    }

    public function provide($config)
    {
        return $this->configLoader->loadConfig($config, $this->crateMap);
    }

    public function getEnvConfigPath()
    {
        return sprintf('%s/%s.php', $this->getConfigDir(), $this->getAppEnv());
    }

    public function getCrateMap()
    {
        return $this->crateMap;
    }

    public function __call($method, $arguments)
    {
        if (!method_exists($this->configLoader, $method)) {
            throw new RuntimeError(
                sprintf(
                    'Method "%s" does not exist on "%s" or "%s".',
                    $method,
                    get_class($this),
                    get_class($this->configLoader)
                )
            );
        }

        return call_user_func_array(array($this->configLoader, $method), $arguments);
    }
}
