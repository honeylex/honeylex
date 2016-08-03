<?php

namespace Honeybee\FrameworkBinding\Silex;

use Honeybee\EnvironmentInterface;
use Honeybee\Infrastructure\Config\ConfigInterface;

class Environment implements EnvironmentInterface
{
    protected $user;

    protected $config;

    public function __construct(ConfigInterface $config, $user = null)
    {
        $this->user = $user;
        $this->config = $config;
    }

    public function getUser()
    {
        return $this->user;
    }
}
