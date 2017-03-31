<?php

namespace Honeylex\Console\Command\Config;

use Honeybee\Infrastructure\Config\Settings;
use Honeylex\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ListConfig extends Command
{
    protected function configure()
    {
        $this
            ->setName('config:ls')
            ->setDescription('List configuration settings.')
            ->addArgument(
                'path',
                InputArgument::OPTIONAL,
                'Filter values for a given path.'
            )
            ->addOption(
                'keys',
                null,
                InputOption::VALUE_NONE,
                'Show configuration keys only.'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $settings = [];
        $handlerConfigs = $this->configProvider->getHandlerConfigs();
        $crateMap = $this->configProvider->getCrateMap();

        if ($path = $input->getArgument('path')) {
            $pathParts = explode('.', $path);
            $key = array_shift($pathParts);
            if ($handlerConfigs->has($key.'.yml')) {
                $settings = $this->configProvider->provide($key.'.yml');
                $settings = is_array($settings) ? $settings : $settings->toArray();
                if ($subPath = implode('.', $pathParts)) {
                    if (array_key_exists($subPath, $settings)) {
                        $settings = $settings[$subPath];
                    } else {
                        do {
                            $key = array_shift($pathParts);
                            $settings = $key && array_key_exists($key, (array)$settings) ? $settings[$key] : [];
                        } while (!empty($pathParts));
                    }
                }
                $settings = $settings ? [ $path => $settings ] : [];
            } elseif ($this->configProvider->hasSetting($path)) {
                $values = $this->configProvider->getSetting($path);
                $settings = [ $path => !is_object($values) ? $values : $values->toArray() ];
            } elseif ($crateMap->hasKey($path)) {
                $values = $this->configProvider->getCrateSettings($path);
                $settings = [ $path => !is_object($values) ? $values : $values->toArray() ];
            }

            if (isset($settings[$path]) && $input->getOption('keys')) {
                $settings = is_array($settings[$path]) ? array_keys($settings[$path]) : [];
            }
        } else {
            foreach ($handlerConfigs->getKeys() as $configFile) {
                $values = $this->configProvider->provide($configFile);
                $settings = array_merge(
                    $settings,
                    [ pathinfo($configFile, PATHINFO_FILENAME) => $values ]
                );
            }

            foreach ($crateMap as $crate) {
                $settings = array_merge(
                    $settings,
                    [ $crate->getPrefix() => $this->configProvider->getCrateSettings($crate->getPrefix()) ]
                );
            }

            if ($input->getOption('keys')) {
                $settings = array_keys($settings);
            }
        }

        $this->renderValues($output, $settings);
    }

    private function renderValues(OutputInterface $output, $settings, $indent = 0)
    {
        foreach ($settings as $key => $value) {
            if (is_scalar($value) || is_null($value)) {
                switch (true) {
                    case is_bool($value):
                        $value = $value === true ? "true" : "false";
                        break;
                    case is_null($value):
                        $value = "null";
                        break;
                    case $value === "":
                        $value = '""';
                        break;
                }
                $output->writeln(str_repeat(' ', $indent*2)."<info>$key</info>: $value");
            } elseif (empty($value)) {
                $output->writeln(str_repeat(' ', $indent*2)."<info>$key</info>: []");
            } else {
                $output->writeln(str_repeat(' ', $indent*2)."<info>$key</info>: [");
                $this->renderValues($output, $value, ++$indent);
                $output->writeln(str_repeat(' ', --$indent*2)."]");
            }
        }
    }
}
