<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\OxidEshopUpdateComponent\DatabaseConfig\Migrator;

use OxidEsales\EshopCommunity\Internal\Framework\Config\Dao\ShopConfigurationSettingDaoInterface;
use OxidEsales\EshopCommunity\Internal\Framework\Dao\EntryDoesNotExistDaoException;
use OxidEsales\EshopCommunity\Internal\Framework\DIContainer\Dao\ParameterDaoInterface;
use OxidEsales\EshopCommunity\Internal\Transition\Utility\ContextInterface;
use OxidEsales\OxidEshopUpdateComponent\DatabaseConfig\Fetcher\DatabaseConfigurationFetcherInterface;
use OxidEsales\OxidEshopUpdateComponent\DatabaseConfig\ParametersProvider\ConfigurationParametersProviderInterface;

class DatabaseToContainerConfigurationMigratorWithCleanup extends DatabaseToContainerConfigurationMigrator
{
    public function __construct(
        ContextInterface $context,
        private readonly ConfigurationParametersProviderInterface $configurationParametersProvider,
        DatabaseConfigurationFetcherInterface $configurationFetcher,
        ParameterDaoInterface $containerConfigurationWriter,
        private readonly ShopConfigurationSettingDaoInterface $databaseConfigurationSettingDao
    ) {
        parent::__construct(
            $context,
            $configurationParametersProvider,
            $configurationFetcher,
            $containerConfigurationWriter
        );
    }

    protected function migrateConfigurationForShop(int $shopId): void
    {
        parent::migrateConfigurationForShop($shopId);

        foreach (
            $this->configurationParametersProvider->getDatabaseConfigurationParameters() as $databaseParameterName
        ) {
            try {
                $configurationSetting = $this->databaseConfigurationSettingDao->get($databaseParameterName, $shopId);

                $this->databaseConfigurationSettingDao->delete($configurationSetting);
            } catch (EntryDoesNotExistDaoException) {
            }
        }
    }
}
