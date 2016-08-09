<?php

namespace Honeybee\FrameworkBinding\Silex\Config\Handler;

class EventBusConfigHandler extends ArrayConfigHandler
{
    protected function handleConfigFile($configFile)
    {
        return $this->parse($configFile);
    }

    protected function mergeConfigs(array $out, array $in)
    {
        if (isset($in['transports'])) {
            foreach ($in['transports'] as $name => $transportConfig) {
                $out['transports'][$name] = $transportConfig;
            }
        }

        if (isset($in['channels'])) {
            foreach ($in['channels'] as $name => $channelConfig) {
                if (!isset($out['channels'][$name])) {
                    $out['channels'][$name] = $channelConfig;
                } else {
                    $out['channels'][$name] = array_merge($out['channels'][$name], $channelConfig);
                }
            }
        }

        return $out;
    }
}
