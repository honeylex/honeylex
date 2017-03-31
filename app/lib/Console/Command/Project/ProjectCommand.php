<?php

namespace Honeylex\Console\Command\Project;

use Honeylex\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Yaml;

abstract class ProjectCommand extends Command
{
    protected function generateSettings(OutputInterface $output, array $settings)
    {
        $settingsFile = $this->configProvider->getConfigDir().'/settings.yml';
        $currentSettings = Yaml::parse(file_get_contents($settingsFile));
        $mergedSettings = array_replace_recursive($currentSettings, $settings);

        (new Filesystem)->dumpFile(
            $settingsFile,
            sprintf($this->getSettingsTemplate(), Yaml::dump($mergedSettings, 8, 2))
        );

        $output->writeln('');
        $output->writeln('Project settings updated in ' . $settingsFile);
        $output->writeln('');
        $output->writeln('    If this is a new project, you can scaffold a crate with:');
        $output->writeln('');
        $output->writeln('    composer honeylex crate:mk <Vendor> <Package>');
        $output->writeln('');
        $output->writeln('    Then you can generate resources with:');
        $output->writeln('');
        $output->writeln('    composer honeylex resource:mk vendor.package <Resource>');
        $output->writeln('');
        $output->writeln('    Run the following command to review your pending migrations:');
        $output->writeln('');
        $output->writeln('    composer honeylex migrate:ls');
        $output->writeln('');
        $output->writeln('    All available console commands are listed here:');
        $output->writeln('');
        $output->writeln('    composer honeylex');
        $output->writeln('');
        $output->writeln('    Please review and modify configuration as required! Happy scaling ;)');
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
