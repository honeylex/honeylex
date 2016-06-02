<?php

namespace Honeybee\FrameworkBinding\Silex\Crate;

class CrateManifest implements CrateManifestInterface
{
    private $name;

    private $prefix;

    private $class;

    private $description;

    public function __construct($rootDir, $name, $prefix, $class, $description = '')
    {
        $this->rootDir = $rootDir;
        $this->name = $name;
        $this->prefix = $prefix;
        $this->class = $class;
        $this->description = $description;
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
        return $this->prefix;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getDescription()
    {
        return $this->description;
    }
}
