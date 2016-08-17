<?php

namespace Honeybee\FrameworkBinding\Silex\Console\Command\Resource;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Trellis\CodeGen\Parser\Schema\EntityTypeSchemaXmlParser;

class ListResources extends ResourceCommand
{
    protected function configure()
    {
        $this
            ->setName('hlx:res:ls')
            ->setDescription('Lists all resources within a crate.')
            ->addArgument(
                'crate',
                InputArgument::OPTIONAL,
                'The prefix of the crate to remove the resource from.'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $cratePrefix = $input->getArgument('crate');

        if ($cratePrefix) {
            $crates = [ $cratePrefix ];
        } else {
            $crates = $this->configProvider->getCrateMap()->getKeys();
        }

        foreach ($crates as $cratePrefix) {
            $crate = $this->configProvider->getCrateMap()->getItem($cratePrefix);
            $finder = clone $this->fileFinder;
            $foundSchemas = $finder->in($crate->getRootDir())->name('aggregate_root.xml');
            $output->writeln($crate->getVendor().'/'.$crate->getName());
            foreach (iterator_to_array($foundSchemas, true) as $fileInfo) {
                $entitySchema = (new EntityTypeSchemaXmlParser)->parse($fileInfo->getPathname());
                $typeDefinition = $entitySchema->getEntityTypeDefinition();
                $output->writeln('- Name: ' . $typeDefinition->getName());
                $output->writeln('  Description: ' . implode(PHP_EOL, $typeDefinition->getDescription()));
            }
        }
    }
}
