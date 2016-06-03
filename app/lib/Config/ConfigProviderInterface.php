<?php

namespace Honeybee\FrameworkBinding\Silex\Config;

interface ConfigProviderInterface
{
    public function provide($config);

    public function getCrateMap();
}
