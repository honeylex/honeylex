<?php

namespace Honeylex\Config\Handler;

class DataAccessConfigHandler extends ArrayConfigHandler
{
    protected function handleConfigFile($configFile)
    {
        $dataAccessConfig = $this->parse($configFile);

        $keys = [ 'units_of_work', 'storage_writers', 'storage_readers', 'finders', 'query_services' ];
        foreach ($keys as $key) {
            if (!isset($dataAccessConfig[$key])) {
                $dataAccessConfig[$key] = [];
            }
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
