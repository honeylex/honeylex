<?php

namespace Honeybee\FrameworkBinding\Silex\Config\Handler;

use Honeybee\FrameworkBinding\Silex\Service\Provisioner\DefaultProvisioner;
use Honeybee\ServiceDefinition;
use Honeybee\ServiceDefinitionMap;

class ServiceConfigHandler extends YamlConfigHandler
{
    public function handle(array $configFiles)
    {
        return array_reduce(
            array_map([ $this, 'handlConfigFile' ], $configFiles), [ $this, 'mergeConfigs' ],
            new ServiceDefinitionMap
        );
    }

    protected function handlConfigFile($configFile)
    {
        $serviceConfigs = $this->parse($configFile) ?: [];
        $serviceDefinitionMap = new ServiceDefinitionMap;

        foreach ($serviceConfigs as $serviceKey => $serviceDefState) {
            $serviceDefState['name'] = $serviceKey;
            if (isset($serviceDefState['provisioner'])) {
                if (!isset($serviceDefState['provisioner']['method'])) {
                    $serviceDefState['provisioner']['method'] = '';
                }
                if (!isset($serviceDefState['provisioner']['class'])) {
                    $serviceDefState['provisioner']['class'] = DefaultProvisioner::CLASS;
                }
            }
            // @todo interpolate $serviceDefState 'setting' values using $this->interpolateConfigValues(...)
            $serviceDefinitionMap->setItem($serviceKey, new ServiceDefinition($serviceDefState));
        }

        return $serviceDefinitionMap;
    }

    protected function mergeConfigs(ServiceDefinitionMap $out, ServiceDefinitionMap $in)
    {
        return $out->append($in);
    }
}
