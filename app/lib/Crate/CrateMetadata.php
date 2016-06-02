<?php

namespace Honeybee\FrameworkBinding\Silex\Crate;

use Honeybee\Infrastructure\Config\SettingsInterface;

class CrateMetadata implements CrateMetadataInterface
{
    private $class;

    private $prefix;

    private $settings;

    public function __construct($prefix, $class, SettingsInterface $settings)
    {
        $this->class = $class;
        $this->prefix = $prefix;
        $this->settings = $settings;
    }

    public function getClass()
    {
        return $this->class;
    }

    public function getPrefix()
    {
        return $this->prefix;
    }

    public function getSettings()
    {
        return $this->settings;
    }
}
