<?php

namespace Honeybee\FrameworkBinding\Silex\Config\Handler;

class DataAccessConfigHandler extends YamlConfigHandler
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
        $dataAccessConfig = $this->parse($configFile);

        $keys = [ 'units_of_work', 'storage_writers', 'storage_readers', 'finders', 'query_services' ];
        foreach ($keys as $key) {
            if (!isset($dataAccessConfig[$key])) {
                $dataAccessConfig[$key] = [];
            }
            // @todo interpolate dbal component setting with $this->interpolateConfigValues(...)
        }
        return $dataAccessConfig;
    }

    protected function mergeConfigs(array $out, array $in)
    {
        foreach ($in as $key => $value) {
            if (!isset($out[$key])) {
                $out[$key] = [];
            }
            $out[$key] = array_merge($out[$key], $value);
        }
        return $out;
    }
}
