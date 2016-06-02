<?php

namespace Honeybee\FrameworkBinding\Silex\Crate;

interface CrateMetadataInterface
{
    public function getClass();

    public function getPrefix();

    public function getSettings();
}
