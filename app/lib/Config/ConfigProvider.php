<?php

namespace Honeybee\FrameworkBinding\Silex\Config;

use Honeybee\FrameworkBinding\Silex\Crate\CrateMap;
use Honeybee\Infrastructure\Config\ArrayConfig;
use Honeybee\Infrastructure\Config\ConfigInterface;
use Honeybee\Infrastructure\Config\Settings;
use Honeybee\Infrastructure\Config\SettingsInterface;
use Symfony\Component\Finder\Finder;

class ConfigProvider implements ConfigProviderInterface
{
    private $handlerConfigs;

    private $crateMap;

    private $settings;

    private $fileFinder;

    public function __construct(
        SettingsInterface $settings,
        CrateMap $crateMap,
        ConfigInterface $handlerConfigs,
        Finder $fileFinder
    ) {
        $this->handlerConfigs = $handlerConfigs;
        $this->fileFinder = $fileFinder;
        $this->settings = $settings;
        $this->crateMap = $crateMap;
        // use internal loading mechanism to load additional settings from crates etc.
        $this->settings = new Settings($this->provide('settings.yml'));
    }

    public function getVersion()
    {
        return $this->settings->get('version');
    }

    public function getEnvConfigPath()
    {
        return sprintf('%s/%s.php', $this->getConfigDir(), $this->getAppEnv());
    }

    public function getCrateMap()
    {
        return $this->crateMap;
    }

    public function getAppContext()
    {
        return $this->settings->get('appContext');
    }

    public function getAppEnv()
    {
        return $this->settings->get('appEnv');
    }

    public function getSetting($setting, $default = null, $ignorePath = false)
    {
        if ($ignorePath) {
            return $this->settings->get($setting, $default);
        }

        $pathParts = explode('.', $setting);
        $value = $this->settings;

        // crate config support
        if (count($pathParts) > 1) {
            $cratePrefix = $pathParts[0].'.'.$pathParts[1];
            if ($this->crateMap->hasKey($cratePrefix)) {
                $pathParts = array_slice($pathParts, 2);
                $value = $this->crateMap->getItem($cratePrefix)->getSettings();
            }
        }

        do {
            $key = array_shift($pathParts);
            $value = $key && $value instanceof SettingsInterface ? $value->get($key) : null;
        } while (!empty($pathParts));

        return is_null($value) ? $default : $value;
    }

    public function getSettings()
    {
        return $this->settings;
    }

    public function hasSetting($setting)
    {
        return $this->settings->has($setting);
    }

    public function getProjectDir()
    {
        return $this->settings->get('project')->get('dir');
    }

    public function getCoreDir()
    {
        return $this->settings->get('core')->get('dir');
    }

    public function getConfigDir()
    {
        return $this->settings->get('project')->get('config_dir');
    }

    public function getCoreConfigDir()
    {
        return $this->settings->get('core')->get('config_dir');
    }

    public function getLocalConfigDir()
    {
        return $this->settings->get('project')->get('local_config_dir');
    }

    public function provide($name)
    {
        $handlerDef = $this->handlerConfigs->get($name);
        $handlerClass = $handlerDef->get('handler');
        $handlerConfig = (array)$handlerDef->get('settings', []);
        $configHandler = new $handlerClass(new ArrayConfig($handlerConfig), $this);

        $handlerConfigsFiles = [
            $this->getCoreConfigDir() . '/' . $name,
            $this->getConfigDir() . '/' . $name
        ];

        foreach ($this->crateMap as $prefix => $crate) {
            $finder = clone $this->fileFinder;
            $wildcard_name = str_replace('.', '*.', $name);
            $foundConfigs = $finder->in($crate->getConfigDir())->name($wildcard_name);
            foreach (iterator_to_array($foundConfigs, true) as $fileInfo) {
                $handlerConfigsFiles[] = $fileInfo->getPathname();
            }
        }

        return $configHandler->handle(
            array_values(
                array_filter(
                    array_unique($handlerConfigsFiles),
                    'is_readable'
                )
            )
        );
    }
}
