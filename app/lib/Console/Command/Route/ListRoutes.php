<?php

namespace Honeybee\FrameworkBinding\Silex\Console\Command\Route;

use Honeybee\FrameworkBinding\Silex\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ListRoutes extends Command
{
    protected function configure()
    {
        $this
            ->setName('hlx:route:ls')
            ->setDescription('List registered routes.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $app = $this->getApplication()->getContainer();
        foreach ($app['routes']->all() as $route) {
            $methods = implode(',', $route->getMethods());
            $output->writeln(($methods ?: 'ANY').' '.$route->getPath());
        }
    }
}
