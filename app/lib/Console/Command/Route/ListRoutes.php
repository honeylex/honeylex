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
            ->setName('route:ls')
            ->setDescription('List registered routes.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $app = $this->getApplication()->getContainer();
        foreach ($app['routes']->all() as $binding => $route) {
            $methods = implode(',', $route->getMethods());
            $output->writeln("<info>$binding</info> => <comment>".($methods ?: 'ANY').'</comment> '.$route->getPath());
        }
    }
}
