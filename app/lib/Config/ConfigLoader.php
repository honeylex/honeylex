<?php

namespace Honeybee\FrameworkBinding\Silex\Config;

use Honeybee\Infrastructure\Config\ConfigInterface;

class ConfigLoader
{
    protected $config;

    public function __construct(ConfigInterface $config)
    {
        $this->config = $config;
    }

    public function loadConfig($name)
    {
        $handlerClass = $this->config->get($name)->get('handler');
        $handler = new $handlerClass;
        $filePath = $this->config->get('core.config_dir') . '/' . $name;

        return $handler->handle($filePath);
    }
}
