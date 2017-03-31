<?php

namespace Honeylex\Crate;

interface CrateManifestInterface
{
    public function getClass();

    public function getPrefix();

    public function getVendor();

    public function getName();

    public function getNamespace();

    public function getRootDir();

    public function getDescription();

    public function getSettings();
}
