<?php

namespace Honeybee\FrameworkBinding\Silex\Crate;

use Silex\Api\ControllerProviderInterface;

interface CrateInterface extends ControllerProviderInterface
{
    public function getPrefix();

    public function getRootDir();

    public function getConfigDir();

    public function getMetadata();
}
