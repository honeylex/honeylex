<?php

namespace Honeybee\FrameworkBinding\Silex\Environment;

use Honeybee\EnvironmentInterface;
use Honeybee\Infrastructure\Config\ConfigInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Core\User\User;

class Environment implements EnvironmentInterface
{
    protected $user;

    protected $config;

    protected $logger;

    public function __construct(User $user, ConfigInterface $config, LoggerInterface $logger)
    {
        $this->user = $user;
        $this->config = $config;
        $this->logger = $logger;
    }

    public function getUser()
    {
        return $this->user;
    }
}
