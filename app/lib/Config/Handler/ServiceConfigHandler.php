<?php

namespace Honeybee\FrameworkBinding\Silex\Config\Handler;

use Honeybee\Common\Error\ConfigError;
use Honeybee\Infrastructure\Config\ConfigInterface;
use Honeybee\ServiceDefinition;
use Honeybee\ServiceDefinitionInterface;
use Honeybee\ServiceDefinitionMap;
use Symfony\Component\Yaml\Parser;

class ServiceConfigHandler
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
            new ServiceDefinitionMap
        );
    }

    protected function handlConfigFile($configFile)
    {
        $serviceConfigs = $this->yamlParser->parse(file_get_contents($configFile));
        $serviceDefinitionMap = new ServiceDefinitionMap;

        foreach ($serviceConfigs as $serviceKey => $serviceDefState) {
            $serviceDefState['name'] = $serviceKey;
            if (isset($serviceDefState['provisioner']) && !isset($serviceDefState['provisioner']['method'])) {
                $serviceDefState['provisioner']['method'] = '';
            }
            $serviceDefinitionMap->setItem($serviceKey, new ServiceDefinition($serviceDefState));
        }

        return $serviceDefinitionMap;
    }

    protected function mergeConfigs(ServiceDefinitionMap $out, ServiceDefinitionMap $in)
    {
        return $out->append($in);
    }

    protected function createParser()
    {
        $parserClass = $this->config->get('parser');

        return new $parserClass;
    }
}
