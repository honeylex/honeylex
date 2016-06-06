<?php

namespace Honeybee\FrameworkBinding\Silex\Crate;

interface CrateManifestInterface
{
    public function getClass();

    public function getPrefix();

    public function getVendor();

    public function getName();

    public function getNamespace();

    public function getRootDir();

    public function getDescription();
}
