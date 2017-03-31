<?php

namespace Honeylex\Config\Handler;

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
