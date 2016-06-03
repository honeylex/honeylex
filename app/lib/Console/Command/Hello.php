<?php

namespace Honeybee\FrameworkBinding\Silex\Console\Command;

use Honeybee\SystemAccount\User\HelloService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

// example command, showing an example of injecting a custom service
class Hello extends Command
{
    public function __construct(HelloService $helloService)
    {
        $this->helloService = $helloService;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('hlx:hello')
            ->setDescription('Command example showing custom service injection (HelloService).');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<info>' . $this->helloService->greet() . '</info>');
    }
}
