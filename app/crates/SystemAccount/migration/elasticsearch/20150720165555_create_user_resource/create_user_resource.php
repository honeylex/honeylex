<?php

namespace Foh\SystemAccount\Migration\Elasticsearch;

use Honeybee\Infrastructure\Migration\ElasticsearchMigration;
use Honeybee\Infrastructure\Migration\MigrationTargetInterface;
use Honeybee\Infrastructure\Migration\MigrationInterface;

class Migration_20150720165555_CreateUserResource extends ElasticsearchMigration
{
    protected function up(MigrationTargetInterface $migration_target)
    {
        $this->updateMappings($migration_target);
    }

    protected function down(MigrationTargetInterface $migration_target)
    {
    }

    public function getDescription($direction = MigrationInterface::MIGRATE_UP)
    {
        if ($direction === MigrationInterface::MIGRATE_UP) {
            return 'Will create the elasticsearch index for the SystemAccount/User context\'s view data.';
        }
    }

    public function isReversible()
    {
        return false;
    }

    protected function getIndexSettingsPath(MigrationTargetInterface $migration_target)
    {
    }

    protected function getTypeMappingPaths(MigrationTargetInterface $migration_target)
    {
        return [
            'foh-system_account-user-standard' => __DIR__ . DIRECTORY_SEPARATOR . 'user-standard-20150720165555-mapping.json'
        ];
    }
}
