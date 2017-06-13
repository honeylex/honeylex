<?php

use Honeylex\Console\App;
use Honeylex\Console\Command\Config\ListConfig;
use Honeylex\Console\Command\Crate\CrateInfo;
use Honeylex\Console\Command\Crate\ListCrates;
use Honeylex\Console\Command\Crate\MakeCrate;
use Honeylex\Console\Command\Crate\RemoveCrate;
use Honeylex\Console\Command\Event\ReplayEvents;
use Honeylex\Console\Command\Fixture\ImportFixture;
use Honeylex\Console\Command\Migrate\ListTargets;
use Honeylex\Console\Command\Migrate\MigrateDown;
use Honeylex\Console\Command\Migrate\MigrateUp;
use Honeylex\Console\Command\Migrate\TargetInfo;
use Honeylex\Console\Command\Project\ConfigureProject;
use Honeylex\Console\Command\Resource\GenerateCode;
use Honeylex\Console\Command\Resource\GenerateCommand;
use Honeylex\Console\Command\Resource\ListResources;
use Honeylex\Console\Command\Resource\MakeResource;
use Honeylex\Console\Command\Resource\RemoveResource;
use Honeylex\Console\Command\Resource\ResourceInfo;
use Honeylex\Console\Command\Route\ListRoutes;
use Honeylex\Console\Command\Worker\RunWorker;
use Symfony\Component\Console\Input\ArgvInput;

$basedir = getcwd() ?: dirname(__DIR__);
require_once $basedir.'/vendor/autoload.php';

$appContext = 'console';

$appVersion = getEnv('APP_VERSION') ?: 'master';
$appEnv = (new ArgvInput)->getParameterOption([ '--env', '-e' ], getenv('APP_ENV') ?: 'dev');
$appDebug = (new ArgvInput)->getParameterOption('--debug', getenv('APP_DEBUG') ?: true);
$hostPrefix = (new ArgvInput)->getParameterOption([ '--host', '-h' ], getenv('HOST_PREFIX'));
$localConfigDir = getenv('LOCAL_CONFIG_DIR') ?: '/usr/local/env';

$app = require $basedir.'/app/bootstrap.php';
$app->boot();
$app->flush();

$appCommands = [
    // Config
    ListConfig::CLASS,
    // Crate
    CrateInfo::CLASS,
    ListCrates::CLASS,
    MakeCrate::CLASS,
    RemoveCrate::CLASS,
    // Fixture
    ImportFixture::CLASS,
    // Migrate
    ListTargets::CLASS,
    MigrateDown::CLASS,
    MigrateUp::CLASS,
    TargetInfo::CLASS,
    // Project
    ConfigureProject::CLASS,
    // Replay
    ReplayEvents::CLASS,
    // Resource
    GenerateCode::CLASS,
    GenerateCommand::CLASS,
    ListResources::CLASS,
    MakeResource::CLASS,
    RemoveResource::CLASS,
    ResourceInfo::CLASS,
    // Route
    ListRoutes::CLASS,
    // Worker
    RunWorker::CLASS
];

set_time_limit(0);

$appState = [ ':app' => $app, ':appCommands' => $appCommands ];
$app['honeybee.service_locator']->make(App::CLASS, $appState)->run();
