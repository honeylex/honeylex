<?php

namespace Honeybee\FrameworkBinding\Silex\Console\Command\Crate;

use Honeybee\Common\Util\StringToolkit;
use Honeybee\FrameworkBinding\Silex\Console\Scaffold\SkeletonGenerator;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class MakeCrate extends CrateCommand
{
    protected function configure()
    {
        $this
            ->setName('crate:mk')
            ->setDescription('Makes a crate from a template.')
            ->addArgument(
                'vendor',
                InputArgument::OPTIONAL,
                'The vendor that ships this crate.'
            )
            ->addArgument(
                'name',
                InputArgument::OPTIONAL,
                'The name of the crate to make.'
            )
            ->addArgument(
                'path',
                InputArgument::OPTIONAL,
                'The directory path where the crate shall be created.'
            )
            ->addOption(
                'description',
                null,
                InputOption::VALUE_REQUIRED,
                'A short text describing the crate\'s purpose.'
            )
            ->addOption(
                'location',
                null,
                InputOption::VALUE_IS_ARRAY | InputOption::VALUE_REQUIRED,
                'Optional override of the locations that will be searched for (crate)skeletons.'
            );
    }

    protected function writeHeader(OutputInterface $output)
    {
        $output->writeln('');
        $output->writeln('Honeylex crate scaffolding');
        $output->writeln('--------------------------');
        $output->writeln('');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $helper = $this->getHelper('question');

        if (!$vendor = $input->getArgument('vendor')) {
            $this->writeHeader($output);
            $question = new Question('Please provide a vendor name: ');
            $vendor = $helper->ask($input, $output, $question);
        }

        if (!$name = $input->getArgument('name')) {
            $question = new Question('Please provide a crate name: ');
            $name = $helper->ask($input, $output, $question);
        }

        if (!$path = $input->getArgument('path')) {
            $question = new Question('Please provide a target path [./app/crates]: ', './app/crates');
            $path = $helper->ask($input, $output, $question);
        }

        $vendor_prefix = StringToolkit::asSnakecase($vendor);
        $name_prefix = StringToolkit::asSnakecase($name);

        if (!$vendor || !$name || !$path) {
            $output->writeln('<error>You must specify a vendor, crate and path.</error>');
            return false;
        }

        $path .= '/'.$vendor_prefix.'-'.$name_prefix;
        $fqns = sprintf('%s\%s', trim($vendor), trim($name));
        $prefix = $vendor_prefix.'.'.$name_prefix;
        $description = $input->getOption('description');
        $locations = $input->getOption('location') ?: null;

        if (!is_array($locations)) {
            $skeletonLocations = [];
            $projectSkeletonDir = $this->configProvider->getProjectDir().'/var/skeletons';
            if (is_readable($projectSkeletonDir)) {
                $skeletonLocations[] = $projectSkeletonDir;
            }
            $coreSkeletonDir = $this->configProvider->getCoreDir().'/var/skeletons';
            if (is_readable($coreSkeletonDir)) {
                $skeletonLocations[] = $coreSkeletonDir;
            }
        } else {
            $skeletonLocations = $locations;
        }
        $skeletonLocations = array_unique($skeletonLocations);

        $output->writeln('Crate vendor/name: '.$vendor.'/'.$name);
        $output->writeln('Crate prefix: '.$prefix);
        $output->writeln('Crate namespace: '.$fqns);
        $output->writeln('Crate description: '.$description);
        $output->writeln('Crate dir: '.$path);
        $output->writeln('Skeleton locations:');
        foreach ($skeletonLocations as $skeletonLocation) {
            $output->writeln('  - '.$skeletonLocation);
        }

        // substitution vars that will be available for skeleton file/directory names and within templates
        $data = [
            'timestamp' => date('YmdHis'),
            'crate' => [
                'vendor' => $vendor,
                'name' => $name,
                'prefix' => $prefix,
                'namespace' => $fqns,
                'description' => $description
            ]
        ];

        // generate crate from skeleton and deploy the resulting code to the target path
        $skeletonGenerator = new SkeletonGenerator($this->configProvider, 'crate', $skeletonLocations, $path, $data);
        $skeletonGenerator->generate();

        // update the composer.json's autoload
        $this->addAutoloadConfig($fqns, $path.'/lib/');

        // update the crates.yml
        $crates = [];
        foreach ($this->configProvider->getCrateMap() as $crateToLoad) {
            $crates[get_class($crateToLoad)]['settings'] = $crateToLoad->getSettings()->toArray();
        }
        $crates[$fqns.'\\'.$name.'Crate']['settings'] = [];
        $this->updateCratesConfig($crates);

        $this->dumpAutoload($output);
    }
}
