<?php

namespace Honeybee\FrameworkBinding\Silex\Console\Command\Resource;

use Honeybee\Common\Util\StringToolkit;
use Honeybee\FrameworkBinding\Silex\Console\Scafold\SkeletonGenerator;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

class RemoveResource extends ResourceCommand
{
    protected function configure()
    {
        $this
            ->setName('hlx:res:rm')
            ->setDescription('Removes a specific resource from the given crate.')
            ->addArgument(
                'crate',
                InputArgument::REQUIRED,
                "The prefix of the crate to remove the resource from."
            )
            ->addArgument(
                'resource',
                null,
                InputArgument::REQUIRED,
                "The name of the resource to remove."
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $cratePrefix = $input->getArgument('crate');
        $resourceName = $input->getArgument('resource');
        $crate = $this->configProvider->getCrateMap()->getItem($cratePrefix);
        if (!$resourceName || !$cratePrefix || !$crate) {
            $output->writeln('<error>You must specify at least a crate-prefix and resource-name.</error>');
            return false;
        }

        $crateDir = $crate->getRootDir();
        $resourceDirectories = [
            $crate->getRootDir().'config/'.$resourceName,
            $crate->getRootDir().'lib/'.$resourceName,
            $crate->getRootDir().'templates/'.StringToolkit::asSnakeCase($resourceName)
        ];
        foreach ($resourceDirectories as $resourceDirectory) {
            $output->writeln('<info>Removing resource dir '.$resourceDirectory.'</info>');
            (new Filesystem)->remove($resourceDirectory);
        }
    }
}