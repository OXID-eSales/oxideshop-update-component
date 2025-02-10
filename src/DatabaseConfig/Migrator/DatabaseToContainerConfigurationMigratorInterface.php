<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\OxidEshopUpdateComponent\DatabaseConfig\Migrator;

interface DatabaseToContainerConfigurationMigratorInterface
{
    public function migrateDatabaseToContainerConfiguration(): void;
}
