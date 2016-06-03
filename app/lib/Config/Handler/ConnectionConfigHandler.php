<?php

namespace Honeybee\FrameworkBinding\Silex\Config\Handler;

use Honeybee\Infrastructure\Config\ConfigInterface;
use Honeybee\Infrastructure\DataAccess\Connector\ConnectorMap;
use Symfony\Component\Yaml\Parser;

class ConnectionConfigHandler implements ConfigHandlerInterface
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
        $connectionConfigs = $this->yamlParser->parse(file_get_contents($configFile));

        return $connectionConfigs;
    }

    protected function mergeConfigs(array $out, array $in)
    {
        return array_merge($out, $in);
    }

    protected function createParser()
    {
        $parserClass = $this->config->get('parser');

        return new $parserClass;
    }
}
