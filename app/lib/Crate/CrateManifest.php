<?php

namespace Honeybee\FrameworkBinding\Silex\Crate;

use Honeybee\Common\Util\StringToolkit;

class CrateManifest implements CrateManifestInterface
{
    private $vendor;

    private $name;

    private $class;

    private $description;

    public function __construct($rootDir, $vendor, $name, $class, $description = '')
    {
        $this->rootDir = $rootDir;
        $this->vendor = $vendor;
        $this->name = $name;
        $this->description = $description;
        $this->class = $class;
    }

    public function getRootDir()
    {
        return $this->rootDir;
    }

    public function getClass()
    {
        return $this->class;
    }

    public function getPrefix()
    {
        return StringToolkit::asSnakecase($this->getVendor()).'.'.StringToolkit::asSnakecase($this->getName());
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
}
