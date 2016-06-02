<?php

namespace Honeybee\SystemAccount\User;

use Honeybee\Infrastructure\Config\ConfigInterface;

class HelloService
{
    protected $config;

    public function __construct(ConfigInterface $config)
    {
        $this->config = $config;
    }

    public function greet()
    {
        $greetings = (array)$this->config->get('greetings', []);

        return $greetings[rand(0, count($greetings) - 1)];
    }
}
