<?php

use Silex\Provider\MonologServiceProvider;
use Silex\Provider\WebProfilerServiceProvider;

// include the prod configuration
require __DIR__.'/prod.php';

$app->register(new WebProfilerServiceProvider(), [
    'profiler.cache_dir' => __DIR__.'/../../var/cache/profiler',
]);
