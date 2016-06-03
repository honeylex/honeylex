<?php

namespace Honeybee\FrameworkBinding\Silex\Console\Command;

use Honeybee\FrameworkBinding\Silex\Config\ConfigProviderInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class Hello extends Command
{
    const NAME = 'honeylex:hello';

    protected $configProvider;

    public function __construct(ConfigProviderInterface $configProvider)
    {
        $this->configProvider = $configProvider;

        parent::__construct(self::NAME);
    }

    protected function configure()
    {
        $this
            ->setDescription('Displays infos about the current setup.')
            ->addOption(
                'info',
                null,
                InputOption::VALUE_NONE,
                "show me a bit more information"
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // @todo gather and print useful information about the current setup
        $output->writeln('<info>This is the honeylex commandline interface.</info>');
        if($input->getOption('info')) {
            $output->writeln('<info>more infos!</info>');
        }
    }
}
