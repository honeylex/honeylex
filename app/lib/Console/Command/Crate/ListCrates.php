<?php

namespace Honeybee\FrameworkBinding\Silex\Console\Command\Crate;

use Honeybee\FrameworkBinding\Silex\Crate\CrateInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ListCrates extends CrateCommand
{
    protected function configure()
    {
        $this
            ->setName('hlx:crate:ls')
            ->setDescription('Lists currently installed crates.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        foreach ($this->configProvider->getCrateMap() as $crate) {
            $this->printCrateInfo($crate, $output);
        }
    }

    protected function printCrateInfo(CrateInterface $crate, OutputInterface $output)
    {
        $vendor = $crate->getVendor();
        $name = $crate->getName();

        $output->writeln(
            sprintf(
                "%s/%s:\n  Prefix: %s\n  Path: %s",
                $vendor,
                $name,
                $crate->getPrefix(),
                $crate->getRootDir()
            )
        );
    }
}
