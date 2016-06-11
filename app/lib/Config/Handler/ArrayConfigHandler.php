<?php

namespace Honeybee\FrameworkBinding\Silex\Config\Handler;

use Honeybee\FrameworkBinding\Silex\Config\ConfigProviderInterface;
use Honeybee\Infrastructure\Config\ConfigInterface;

abstract class ArrayConfigHandler extends YamlConfigHandler
{
    abstract protected function handleConfigFile($configFile);

    abstract protected function mergeConfigs(array $out, array $in);

    public function handle(array $configFiles)
    {
        return $this->interpolateConfigValues(
            array_reduce(
                array_map([ $this, 'handleConfigFile' ], $configFiles),
                [ $this, 'mergeConfigs' ],
                []
            )
        );
    }
}
