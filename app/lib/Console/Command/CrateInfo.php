<?php

namespace Honeybee\FrameworkBinding\Silex\Console\Command;

use Honeybee\FrameworkBinding\Silex\Crate\CrateInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CrateInfo extends CrateCommand
{
    protected function configure()
    {
        $this
            ->setName('hlx:crate:info')
            ->setDescription('Displays details for the specified crate.')
            ->addArgument(
                'crate',
                InputArgument::REQUIRED,
                'prefix of the crate to show the details for'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $prefix = $input->getArgument('crate');
        $crate = $this->configProvider->getCrateMap()->getItem($prefix);
        if ($crate) {
            $this->printCrateInfo($crate, $output);
        } else {
            $output->writeln('<error>Given crate is not installed.</error>');
        }
    }

    protected function printCrateInfo(CrateInterface $crate, OutputInterface $output)
    {
        $output->writeln($crate->getName().": ".$crate->getDescription());
        $output->writeln('---------------------');
        $output->writeln('prefix: '.$crate->getPrefix());
        $output->writeln('namespace: '.$crate->getNamespace());
        $output->writeln('directory: '.$crate->getRootDir());
    }
}
