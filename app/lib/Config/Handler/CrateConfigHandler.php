<?php

namespace Honeybee\FrameworkBinding\Silex\Config\Handler;

use Honeybee\Infrastructure\Config\Settings;
use Honeybee\FrameworkBinding\Silex\Crate\CrateManifest;
use Honeybee\FrameworkBinding\Silex\Crate\CrateManifestMap;
use ReflectionClass;
use Symfony\Component\Yaml\Parser;

class CrateConfigHandler implements ConfigHandlerInterface
{
    public function handle(array $configFiles)
    {
        return $this->handleConfigFiles($configFiles);
    }

    protected function handleConfigFiles(array $configFiles)
    {
        $yamlParser = new Parser;
        $manifests = [];

        $crates = [];
        foreach (array_unique($configFiles) as $configFile) {
            if (is_readable($configFile)) {
                $crates = array_replace_recursive(
                    $crates,
                    (array)$yamlParser->parse(file_get_contents($configFile))
                );
            }
        }

        foreach ($crates as $implementor => $config) {
            $reflector = new ReflectionClass($implementor);
            $crateDir = dirname(dirname($reflector->getFileName()));
            $manifestFile = $crateDir . '/manifest.yml';
            $manifest = $yamlParser->parse(file_get_contents($manifestFile));

            $name = $manifest['name'];
            $vendor = $manifest['vendor'];
            $description = isset($manifest['description']) ? $manifest['description'] : '';
            $settings = isset($config['settings']) ? (array)$config['settings'] : [];
            $metadata = new CrateManifest(
                $crateDir,
                $vendor,
                $name,
                $implementor,
                $description,
                new Settings($settings)
            );
            $manifests[$metadata->getPrefix()] = $metadata;
        }

        return new CrateManifestMap($manifests);
    }
}
