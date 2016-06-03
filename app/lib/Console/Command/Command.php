<?php

namespace Honeybee\FrameworkBinding\Silex\Console\Command;

use Honeybee\FrameworkBinding\Silex\Config\ConfigProviderInterface;
use Symfony\Component\Console\Command\Command as BaseCommand;

abstract class Command extends BaseCommand
{
    protected $configProvider;

    public function __construct(ConfigProviderInterface $configProvider)
    {
        $this->configProvider = $configProvider;

        parent::__construct();
    }
}
