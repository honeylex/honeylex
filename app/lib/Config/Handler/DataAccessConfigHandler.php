<?php

namespace Honeybee\FrameworkBinding\Silex\Config\Handler;

use Honeybee\Infrastructure\Config\ConfigInterface;
use Honeybee\Infrastructure\DataAccess\Connector\ConnectorMap;
use Symfony\Component\Yaml\Parser;

class DataAccessConfigHandler implements ConfigHandlerInterface
{
    protected $config;

    protected $yamlParser;

    public function __construct(ConfigInterface $config)
    {
        $this->config = $config;
        $parserClass = $this->config->get('parser');
        $this->yamlParser = new $parserClass;
    }

    public function handle(array $configFiles)
    {
        return array_reduce(
            array_map([ $this, 'handlConfigFile' ], $configFiles), [ $this, 'mergeConfigs' ],
            []
        );
    }

    protected function handlConfigFile($configFile)
    {
        $dataAccessConfig = $this->yamlParser->parse(file_get_contents($configFile));

        $keys = [ 'units_of_work', 'storage_writers', 'storage_readers', 'finders', 'query_services' ];
        foreach ($keys as $key) {
            if (!isset($dataAccessConfig[$key])) {
                $dataAccessConfig[$key] = [];
            }
        }
        return $dataAccessConfig;
    }

    protected function expandConfigDirectives(array $config)
    {
        foreach ($config as $key => $value) {
            if (is_array($value)) {
                $config[$key] = $this->expandConfigDirectives($value);
            } else if (is_string($value)) {
                $config[$key] = $value; // @todo
            } else {
                $config[$key] = $value;
            }
        }
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

    protected function createParser()
    {
        $parserClass = $this->config->get('parser');

        return new $parserClass;
    }
}
