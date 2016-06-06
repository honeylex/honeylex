<?php

namespace Honeybee\FrameworkBinding\Silex\Config\Handler;

class MigrationConfigHandler extends YamlConfigHandler
{
    public function handle(array $configFiles)
    {
        return $this->interpolateConfigValues(
            array_reduce(
                array_map([ $this, 'handlConfigFile' ], $configFiles), [ $this, 'mergeConfigs' ],
                []
            )
        );
    }

    protected function handlConfigFile($configFile)
    {
        $migrationTargetConfigs = $this->parse($configFile);

        return $migrationTargetConfigs;
    }

    protected function mergeConfigs(array $out, array $in)
    {
        return array_merge($out, $in);
    }
}
