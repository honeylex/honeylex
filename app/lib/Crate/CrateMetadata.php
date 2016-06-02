<?php

namespace Honeybee\FrameworkBinding\Silex\Crate;

use Honeybee\Infrastructure\Config\SettingsInterface;

class CrateMetadata implements CrateMetadataInterface
{
    private $class;

    private $prefix;

    private $settings;

    public function __construct($prefix, $class, SettingsInterface $manifest)
    {
        $this->class = $class;
        $this->prefix = $prefix;
        $this->manifest = $manifest;
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
