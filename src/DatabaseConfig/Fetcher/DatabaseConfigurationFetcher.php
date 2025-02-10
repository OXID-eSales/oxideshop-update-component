<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\OxidEshopUpdateComponent\DatabaseConfig\Fetcher;

use OxidEsales\EshopCommunity\Internal\Framework\Config\Dao\ShopConfigurationSettingDaoInterface;
use OxidEsales\EshopCommunity\Internal\Framework\Dao\EntryDoesNotExistDaoException;

readonly class DatabaseConfigurationFetcher implements DatabaseConfigurationFetcherInterface
{
    public function __construct(private ShopConfigurationSettingDaoInterface $databaseConfigurationSettingDao)
    {
    }

    public function fetchDatabaseConfigurationValues(int $shopId, array $databaseParameterNames): array
    {
        $configuration = [];
        foreach ($databaseParameterNames as $parameterName) {
            try {
                $configuration[$parameterName] = $this->databaseConfigurationSettingDao
                    ->get($parameterName, $shopId)
                    ->getValue();
            } catch (EntryDoesNotExistDaoException) {
            }
        }

        return $configuration;
    }
}
