<?php

namespace Honeybee\FrameworkBinding\Silex\Crate;

use Honeybee\FrameworkBinding\Silex\Crate\CrateManifestMap;
use Silex\Application;

interface CrateLoaderInterface
{
    public function loadCrates(Application $app, CrateManifestMap $crateManifestMap);
}
