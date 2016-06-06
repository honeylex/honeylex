<?php

namespace Foh\SystemAccount\Migration\CouchDb;

use Honeybee\Infrastructure\Migration\CouchDbMigration;
use Honeybee\Infrastructure\Migration\MigrationTargetInterface;
use Honeybee\Infrastructure\Migration\MigrationInterface;

class Migration_20140915235807_InitEventSource extends CouchDbMigration
{
    protected function up(MigrationTargetInterface $migration_target)
    {
        $this->createDatabaseIfNotExists($migration_target);
    }

    protected function down(MigrationTargetInterface $migration_target)
    {
        $this->deleteDatabase($migration_target);
    }

    public function getDescription($direction = MigrationInterface::MIGRATE_UP)
    {
        if ($direction === MigrationInterface::MIGRATE_UP) {
            return 'Will initially setup a couchdb database for the Foh\SystemAccount\User context.';
        }
        return 'Will deinit the couchdb database for the Foh\SystemAccount\User context.';
    }

    public function isReversible()
    {
        return true;
    }

    protected function getViewsDirectory()
    {
    }

    protected function getDesignDocName()
    {
    }
}
