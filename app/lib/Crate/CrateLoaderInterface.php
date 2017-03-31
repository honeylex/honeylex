<?php

namespace Honeylex\Crate;

use Honeylex\Crate\CrateManifestMap;
use Silex\Application;

interface CrateLoaderInterface
{
    public function loadCrates(Application $app, CrateManifestMap $crateManifestMap);
}
