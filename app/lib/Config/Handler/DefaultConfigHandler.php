<?php

namespace Honeybee\FrameworkBinding\Silex\Config\Handler;

class DefaultConfigHandler extends ArrayConfigHandler
{
    protected function handleConfigFile($configFile)
    {
        return $this->parse($configFile);
    }

    protected function mergeConfigs(array $out, array $in)
    {
        return array_replace_recursive($out, $in);
    }
}
