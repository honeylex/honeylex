<?php

namespace Honeylex;

use Honeybee\EnvironmentInterface;
use Honeybee\Infrastructure\Config\ConfigInterface;
use Silex\Application;

class Environment implements EnvironmentInterface
{
    protected $app;

    protected $config;

    public function __construct(Application $app, ConfigInterface $config)
    {
        $this->app = $app;
        $this->config = $config;
    }

    public function getUser()
    {
        if (isset($this->app['security.token_storage'])) {
            $token = $this->app['security.token_storage']->getToken();
            if ($token) {
                return $token->getUser();
            }
        }
        return null;
    }

    public function getSettings()
    {
        return $this->config;
    }
}
