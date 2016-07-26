<?php

namespace Honeybee\FrameworkBinding\Silex\Console\Command\Project;

use Honeybee\FrameworkBinding\Silex\Console\Command\Command;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Console\Output\OutputInterface;

abstract class ProjectCommand extends Command
{
    protected function install(OutputInterface $output, array $settings)
    {
        $settingsFile = $this->configProvider->getConfigDir().'/settings.yml';
        (new Filesystem($adapter))->dumpFile(
            $settingsFile,
            sprintf($this->getSettingsTemplate(), Yaml::dump($settings, 8, 2))
        );

        $output->writeln('Project settings updated in ' . $settingsFile);
        $output->writeln('');
        $output->writeln('    If this project is new you can scaffold a crate using:');
        $output->writeln('');
        $output->writeln('    bin/console hlx:crate:mk <Vendor> <Package> ./crates');
        $output->writeln('');
        $output->writeln('    Then you can scaffold a resource using:');
        $output->writeln('');
        $output->writeln('    bin/console hlx:res:mk vendor.package <Resource>');
        $output->writeln('');
        $output->writeln('    Run the following command to review your pending migrations:');
        $output->writeln('');
        $output->writeln('    bin/console hlx:migrate:ls');
        $output->writeln('');
    }

    protected function getSettingsTemplate()
    {
        return <<<SETTINGS
#
# Project settings:
---
%s
SETTINGS;
    }
}
