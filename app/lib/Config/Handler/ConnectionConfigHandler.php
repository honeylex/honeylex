<?php

namespace Honeylex\Config\Handler;

class ConnectionConfigHandler extends ArrayConfigHandler
{
    protected function handleConfigFile($configFile)
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
