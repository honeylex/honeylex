<?php

namespace Honeybee\FrameworkBinding\Silex\Crate;

use Honeybee\Common\Util\StringToolkit;
use Honeybee\Infrastructure\Config\Settings;

class CrateManifest implements CrateManifestInterface
{
    private $rootDir;

    private $vendor;

    private $name;

    private $class;

    private $description;

    private $settings;

    public function __construct($rootDir, $vendor, $name, $class, $description = '', Settings $settings = null)
    {
        $this->rootDir = $rootDir;
        $this->vendor = $vendor;
        $this->name = $name;
        $this->class = $class;
        $this->description = $description;
        $this->settings = $settings ? $settings : new Settings;
    }

    public function getRootDir()
    {
        return $this->rootDir;
    }

    public function getClass()
    {
        return $this->class;
    }

    public function getPrefix($sep = '.')
    {
        return StringToolkit::asSnakecase($this->getVendor()).$sep.StringToolkit::asSnakecase($this->getName());
    }

    public function getVendor()
    {
        return $this->vendor;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getNamespace()
    {
        return $this->getVendor().'\\'.$this->getName();
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function getSettings()
    {
        return $this->settings;
    }
}
