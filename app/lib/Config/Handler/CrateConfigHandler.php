<?php

namespace Honeybee\FrameworkBinding\Silex\Config\Handler;

use ReflectionClass;
use Honeybee\Common\Error\ConfigError;
use Honeybee\FrameworkBinding\Silex\Crate\CrateManifest;
use Honeybee\FrameworkBinding\Silex\Crate\CrateManifestMap;
use Honeybee\Infrastructure\Config\ConfigInterface;
use Honeybee\Infrastructure\Config\Settings;
use Honeybee\ServiceDefinition;
use Honeybee\ServiceDefinitionInterface;
use Honeybee\ServiceDefinitionMap;
use Symfony\Component\Yaml\Parser;

class CrateConfigHandler implements ConfigHandlerInterface
{
    public function handle(array $configFiles)
    {
        if (count($configFiles) !== 1) {
            throw new ConfigError('Unsupported number of crate.yml config files given.');
        }

        return $this->handlConfigFile($configFiles[0]);
    }

    protected function handlConfigFile($configFile)
    {
        $yamlParser = new Parser;
        $manifestMap = new CrateManifestMap;
        $crates = $yamlParser->parse(file_get_contents($configFile));

        foreach ($crates as $implementor) {
            $reflector = new ReflectionClass($implementor);
            $crateDir = dirname(dirname($reflector->getFileName()));
            $manifestFile = $crateDir . '/manifest.yml';
            $manifest = $yamlParser->parse(file_get_contents($manifestFile));

            $name = $manifest['name'];
            $vendor = $manifest['vendor'];
            $description = isset($manifest['description']) ? $manifest['description'] : '';
            $metadata = new CrateManifest($crateDir, $vendor, $name, $implementor, $description);
            $manifestMap->setItem($metadata->getPrefix(), $metadata);
        }

        return $manifestMap;
    }
}
