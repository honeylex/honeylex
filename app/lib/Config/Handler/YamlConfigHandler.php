<?php

namespace Honeybee\FrameworkBinding\Silex\Config\Handler;

use Honeybee\FrameworkBinding\Silex\Config\ConfigProviderInterface;
use Honeybee\Infrastructure\Config\ConfigInterface;

abstract class YamlConfigHandler implements ConfigHandlerInterface
{
    protected $configProvider;

    protected $config;

    protected $yamlParser;

    public function __construct(ConfigInterface $config, ConfigProviderInterface $configProvider)
    {
        $this->config = $config;
        $parserClass = $this->config->get('parser');
        $this->yamlParser = new $parserClass;
        $this->configProvider = $configProvider;
    }

    protected function parse($filepath)
    {
        return $this->yamlParser->parse(file_get_contents($filepath));
    }

    protected function interpolateConfigValues(array $config)
    {
        foreach ($config as $key => $value) {
            if (is_array($value)) {
                $config[$key] = $this->interpolateConfigValues($value);
            } else if (is_string($value)) {
                if (preg_match_all('/(\$\{(.*?)\})/', $value, $matches)) {
                    $replacements = [];
                    foreach ($matches[2] as $configKey) {
                        $replacements[] = $this->configProvider->getSetting($configKey);
                    }
                    $config[$key] = str_replace($matches[0], $replacements, $value);
                }
            }
        }

        return $config;
    }
}
