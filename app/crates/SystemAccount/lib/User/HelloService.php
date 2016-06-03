<?php

namespace Honeybee\SystemAccount\User;

use Honeybee\Infrastructure\Config\ConfigInterface;
use Honeybee\SystemAccount\User\Projection\Standard\UserType;

class HelloService
{
    protected $config;

    protected $userType;

    public function __construct(ConfigInterface $config, UserType $userType)
    {
        $this->config = $config;
        $this->userType = $userType;
    }

    public function greet()
    {
        $greetings = (array)$this->config->get('greetings', []);

        return $greetings[rand(0, count($greetings) - 1)];
    }
}
