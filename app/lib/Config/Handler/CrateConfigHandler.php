<?php

namespace Honeybee\FrameworkBinding\Silex\Config\Handler;

use Honeybee\Common\Error\ConfigError;
use Honeybee\FrameworkBinding\Silex\Crate\CrateMetadata;
use Honeybee\FrameworkBinding\Silex\Crate\CrateMetadataMap;
use Honeybee\Infrastructure\Config\ConfigInterface;
use Honeybee\Infrastructure\Config\Settings;
use Honeybee\ServiceDefinition;
use Honeybee\ServiceDefinitionInterface;
use Honeybee\ServiceDefinitionMap;
use Symfony\Component\Yaml\Parser;

class CrateConfigHandler
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
            new CrateMetadataMap
        );
    }

    protected function handlConfigFile($configFile)
    {
        $crateMetadataMap = new CrateMetadataMap;
        $cratesConfig = $this->yamlParser->parse(file_get_contents($configFile));

        foreach ($cratesConfig as $cratePrefix => $crateConfig) {
            $crateClass = $crateConfig['class'];
            unset($crateConfig['class']);
            $crateSettings = new Settings($crateConfig);
            $crateMetadata = new CrateMetadata($cratePrefix, $crateClass, $crateSettings);
            $crateMetadataMap->setItem($cratePrefix, $crateMetadata);
        }

        return $crateMetadataMap;
    }

    protected function mergeConfigs(CrateMetadataMap $out, CrateMetadataMap $in)
    {
        return $out->append($in);
    }
}
