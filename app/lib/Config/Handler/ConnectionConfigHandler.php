<?php

namespace Honeybee\FrameworkBinding\Silex\Config\Handler;

class ConnectionConfigHandler extends YamlConfigHandler
{
    public function handle(array $configFiles)
    {
        return array_reduce(
            array_map([ $this, 'handlConfigFile' ], $configFiles), [ $this, 'mergeConfigs' ],
            []
        );
    }

    protected function handlConfigFile($configFile)
    {
        $connectionConfigs = $this->parse($configFile);
        foreach ($connectionConfigs as &$connectionConfig) {
            if (!isset($connectionConfig['settings'])) {
                $connectionConfig['settings'] = [];
            } else {
                $connectionConfig['settings'] = $this->interpolateConfigValues($connectionConfig['settings']);
            }
        }
        return $connectionConfigs;
    }

    protected function mergeConfigs(array $out, array $in)
    {
        return array_merge($out, $in);
    }
}
