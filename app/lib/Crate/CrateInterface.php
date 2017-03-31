<?php

namespace Honeylex\Crate;

use Silex\Api\ControllerProviderInterface;

interface CrateInterface extends ControllerProviderInterface
{
    public function getConfigDir();

    public function getManifest();

    public function getRoutingPrefix();
}
