<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\OxidEshopUpdateComponent\DatabaseConfig\Migrator;

class DatabaseMigrationFactory
{
    public function __construct(
        private readonly DatabaseToContainerConfigurationMigrator $migrationService,
        private readonly DatabaseToContainerConfigurationMigratorWithCleanup $migrationWithCleanupService
    ) {
    }

    public function createMigrationService(
        bool $shouldRemoveOldParameters
    ): DatabaseToContainerConfigurationMigratorInterface {
        return $shouldRemoveOldParameters ? $this->migrationWithCleanupService : $this->migrationService;
    }
}
