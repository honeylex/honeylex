<?php

namespace Honeybee\FrameworkBinding\Silex\Console\Command\Resource;

use Honeybee\Common\Util\StringToolkit;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
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
                InputArgument::REQUIRED,
                "The name of the resource to remove."
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $cratePrefix = $input->getArgument('crate');
        $resourceName = $input->getArgument('resource');

        if (!$resourceName || !$cratePrefix) {
            $output->writeln('<error>You must specify at least a crate-prefix and resource-name.</error>');
            return false;
        }

        $crate = $this->configProvider->getCrateMap()->getItem($cratePrefix);

        $crateDir = $crate->getRootDir();
        $resourceDirectories = [
            $crate->getRootDir().'/config/'.$resourceName,
            $crate->getRootDir().'/lib/'.$resourceName,
            $crate->getRootDir().'/templates/'.StringToolkit::asSnakeCase($resourceName)
        ];
        // @todo tricky: find and remove proper migration directories
        foreach ($resourceDirectories as $resourceDirectory) {
            $output->writeln('<info>Removing resource dir '.$resourceDirectory.'</info>');
            (new Filesystem)->remove($resourceDirectory);
        }
        // @todo also tricky: find and run proper migrations
    }
}
