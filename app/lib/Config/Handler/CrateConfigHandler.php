<?php

namespace Honeybee\FrameworkBinding\Silex\Config\Handler;

use Honeybee\Common\Error\ConfigError;
use Honeybee\FrameworkBinding\Silex\Crate\CrateMetadata;
use Honeybee\FrameworkBinding\Silex\Crate\CrateMetadataMap;
use Honeybee\Infrastructure\Config\Settings;
use Honeybee\ServiceDefinition;
use Honeybee\ServiceDefinitionInterface;
use Honeybee\ServiceDefinitionMap;
use Symfony\Component\Yaml\Parser;

class CrateConfigHandler
{
    public function handle($configFile)
    {
        $parser = new Parser;
        $crateMetadataMap = new CrateMetadataMap;
        $cratesConfig = $parser->parse(file_get_contents($configFile));

        foreach ($cratesConfig as $cratePrefix => $crateConfig) {
            $crateClass = $crateConfig['class'];
            unset($crateConfig['class']);
            $crateSettings = new Settings($crateConfig);
            $crateMetadata = new CrateMetadata($cratePrefix, $crateClass, $crateSettings);
            $crateMetadataMap->setItem($cratePrefix, $crateMetadata);
        }

        return $crateMetadataMap;
    }
}
