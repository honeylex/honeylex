<?php

namespace Honeylex\Console\Command;

use Honeylex\Config\ConfigProviderInterface;
use Symfony\Component\Console\Command\Command as BaseCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

abstract class Command extends BaseCommand
{
    protected $configProvider;

    public function __construct(ConfigProviderInterface $configProvider)
    {
        $this->configProvider = $configProvider;

        parent::__construct();
    }

    protected function confirm(InputInterface $input, OutputInterface $output)
    {
        $helper = $this->getHelper('question');
        $question = new ConfirmationQuestion('Are you sure? [y\N]: ', false);
        return $helper->ask($input, $output, $question);
    }
}
