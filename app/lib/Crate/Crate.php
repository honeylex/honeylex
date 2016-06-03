<?php

namespace Honeybee\FrameworkBinding\Silex\Crate;

use Honeybee\Common\Error\RuntimeError;
use ReflectionClass;
use Silex\Application;

abstract class Crate implements CrateInterface
{
    private $manifest;

    public function __construct(CrateManifestInterface $manifest)
    {
        $this->manifest = $manifest;
    }

    public function getManifest()
    {
        return $this->manifest;
    }

    public function getConfigDir()
    {
        return $this->getRootDir() . '/config';
    }

    public function __call($method, $arguments)
    {
        if (!method_exists($this->manifest, $method)) {
            throw new RuntimeError(
                sprintf(
                    'Method "%s" does not exist on "%s" or "%s".',
                    $method,
                    get_class($this),
                    get_class($this->manifest)
                )
            );
        }

        return call_user_func_array(array($this->manifest, $method), $arguments);
    }
}
