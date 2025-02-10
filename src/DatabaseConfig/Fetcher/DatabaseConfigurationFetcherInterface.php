<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\OxidEshopUpdateComponent\DatabaseConfig\Fetcher;

interface DatabaseConfigurationFetcherInterface
{
    public function fetchDatabaseConfigurationValues(int $shopId, array $databaseParameterNames): array;
}
