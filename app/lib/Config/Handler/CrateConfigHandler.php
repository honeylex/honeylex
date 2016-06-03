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
        if (count($configFiles) !== 1) {
            throw new ConfigError('Unsupported number of crate.yml config files given.');
        }

        return $this->handlConfigFile($configFiles[0]);
    }

    protected function handlConfigFile($configFile)
    {
        $manifestMap = new CrateManifestMap;
        $crates = $this->yamlParser->parse(file_get_contents($configFile));

        foreach ($crates as $implementor) {
            $reflector = new ReflectionClass($implementor);
            $root = dirname(dirname($reflector->getFileName()));
            $manifestFile = $root . '/manifest.yml';
            $manifest = $this->yamlParser->parse(file_get_contents($manifestFile));

            $name = $manifest['name'];
            $prefix = $manifest['prefix'];
            $description = isset($manifest['description']) ? $manifest['description'] : '';

            $metadata = new CrateManifest($root, $name, $prefix, $implementor, $description);
            $manifestMap->setItem($manifest['prefix'], $metadata);
        }

        return $manifestMap;
    }
}
