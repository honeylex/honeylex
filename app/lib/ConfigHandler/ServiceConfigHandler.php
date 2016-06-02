<?php

namespace Honeybee\FrameworkBinding\Silex\ConfigHandler;

use Honeybee\Common\Error\ConfigError;
use Honeybee\ServiceDefinition;
use Honeybee\ServiceDefinitionInterface;
use Honeybee\ServiceDefinitionMap;
use Symfony\Component\Yaml\Parser;

class ServiceConfigHandler
{
    public function __construct(Parser $parser)
    {
        $this->parser = $parser;
    }

    public function handle($configFile)
    {
        $serviceDefinitionMap = new ServiceDefinitionMap;
        $serviceConfigs = $this->parser->parse(file_get_contents($configFile));

        foreach ($serviceConfigs as $serviceKey => $serviceDefState) {
            $serviceDefState['name'] = $serviceKey;
            if (isset($serviceDefState['provisioner']) && !isset($serviceDefState['provisioner']['method'])) {
                $serviceDefState['provisioner']['method'] = '';
            }
            $serviceDefinitionMap->setItem($serviceKey, new ServiceDefinition($serviceDefState));
        }

        return $serviceDefinitionMap;
    }
}
