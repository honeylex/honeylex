<?php

namespace Honeybee\FrameworkBinding\Silex\Crate;

use ReflectionClass;
use Silex\Application;

abstract class Crate implements CrateInterface
{
    private $rootDir;

    private $metadata;

    public function __construct(CrateMetadataInterface $metadata)
    {
        $this->metadata = $metadata;
    }

    public function getMetadata()
    {
        return $this->metadata;
    }

    public function getRootDir()
    {
        if (!$this->rootDir) {
            $reflector = new ReflectionClass(static::CLASS);
            $this->rootDir = dirname(dirname($reflector->getFileName()));
        }

        return $this->rootDir;
    }

    public function getConfigDir()
    {
        return $this->getRootDir() . '/config';
    }
}
