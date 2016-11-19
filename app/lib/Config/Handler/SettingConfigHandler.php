<?php

namespace Honeybee\FrameworkBinding\Silex\Config\Handler;

use Honeybee\Common\Error\ConfigError;

class SettingConfigHandler extends YamlConfigHandler
{
    public function handle(array $configFiles)
    {
        return $this->interpolateConfigValues(
            array_replace_recursive(
                array_reduce(
                    array_map([ $this, 'handleConfigFile' ], $configFiles),
                    [ $this, 'mergeConfigs' ],
                    []
                ),
                (array)$this->configProvider->getSettings()->toArray()
            )
        );
    }

    protected function handleConfigFile($configFile)
    {
        $settings = [];

        $settingsConfig = $this->yamlParser->parse(file_get_contents($configFile));

        $localConfigs = isset($settingsConfig['local_configs']) ? $settingsConfig['local_configs'] : [];
        unset($settingsConfig['local_configs']);

        foreach ($localConfigs as $prefix => $localConfig) {
            $settings[$prefix] = $this->loadLocalSettings($localConfig);
        }

        foreach ($settingsConfig as $prefix => $rawSettings) {
            $settings[$prefix] = $rawSettings;
        }

        return $settings;
    }

    protected function mergeConfigs(array $out, array $in)
    {
        return array_replace_recursive($out, $in);
    }

    protected function loadLocalSettings(array $localConfig)
    {
        $localConfigDir = $this->configProvider->getLocalConfigDir().DIRECTORY_SEPARATOR;

        if ($localConfig['load'] === 'from_file') {
            $localConfigFile = $localConfigDir.$localConfig['name'];
            $localConfigFiles = [ $localConfigFile ];

            if ($hostPrefix = $this->configProvider->getHostPrefix()) {
                $hostConfigDir = $localConfigDir.$hostPrefix.DIRECTORY_SEPARATOR;
                $localConfigFiles[] = $hostConfigDir.$localConfig['name'];
            }

            $settings = [];
            foreach (array_unique($localConfigFiles) as $configFile) {
                if (is_readable($configFile)) {
                    $settings = array_replace_recursive(
                        $this->handleFileBasedLocalConfig($configFile, $localConfig['type'])
                    );
                }
            }
        }

        // @todo support loading from environment vars

        return $settings;
    }

    protected function handleFileBasedLocalConfig($configFile, $type)
    {
        if ($type === 'yaml') {
            $yamlString = file_get_contents($configFile);
            try {
                $settings = $yaml_parser->parse($yamlString);
            } catch (\Exception $parseError) {
                throw new ConfigError(
                    'Failed to parse yaml for local config file: '.$configFile.PHP_EOL .
                    'Error: '.$parseError->getMessage()
                );
            }
        } elseif ($type === 'json') {
            $jsonString = file_get_contents($configFile);
            $settings = json_decode($jsonString, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new ConfigError('Failed to parse json from file "'.$confgFile.'": '.json_last_error_msg());
            }
        } else {
            throw new ConfigError('Only "yaml" or "json" are supported for "type" setting of local-configs.');
        }

        return $settings;
    }

    protected function interpolateConfigValues(array $config, array $globalConf = null)
    {
        $globalConf = $globalConf ?: $config;
        foreach ($config as $key => $value) {
            if (is_array($value)) {
                $config[$key] = $this->interpolateConfigValues($value, $globalConf);
            } elseif (is_string($value)) {
                if (preg_match_all('/(\$\{(.*?)\})/', $value, $matches)) {
                    $replacements = [];
                    foreach ($matches[2] as $configKey) {
                        $replacements[] = $this->interpolateConfigValue($configKey, $globalConf);
                    }
                    $config[$key] = str_replace($matches[0], $replacements, $value);
                }
            }
        }

        return $config;
    }

    protected function interpolateConfigValue($key, array $globalConf)
    {
        $pathParts = explode('.', $key);
        $value = &$globalConf;

        do {
            $curKey = array_shift($pathParts);
            $value = &$value[$curKey];
        } while (!empty($pathParts));

        return $value;
    }
}
