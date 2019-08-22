<?php declare(strict_types=1);
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\OxidEshopUpdateComponent\Module\Service;

use OxidEsales\EshopCommunity\Internal\Common\Exception\EntryDoesNotExistDaoException;
use OxidEsales\EshopCommunity\Internal\Module\Configuration\Dao\ShopConfigurationDaoInterface;
use OxidEsales\EshopCommunity\Internal\Module\Configuration\DataObject\ShopConfiguration;
use OxidEsales\EshopCommunity\Internal\Module\Setting\SettingDaoInterface;

/**
 * @internal
 */
class ModuleSettingsTransferringService implements ModuleSettingsTransferingServiceInterface
{
    /**
     * @var ShopConfigurationDaoInterface
     */
    private $shopConfigurationDao;

    /**
     * @var SettingDaoInterface
     */
    private $moduleSettingDao;

    /**
     * ModuleSettingsTransferingService constructor.
     * @param ShopConfigurationDaoInterface $shopConfigurationDao
     * @param SettingDaoInterface $moduleSettingDao
     */
    public function __construct(
        ShopConfigurationDaoInterface $shopConfigurationDao,
        SettingDaoInterface $moduleSettingDao
    ) {
        $this->shopConfigurationDao = $shopConfigurationDao;
        $this->moduleSettingDao = $moduleSettingDao;
    }

    public function transferValuesFromDatabaseToProjectConfiguration(): void
    {
        foreach ($this->shopConfigurationDao->getAll() as $shopId => $shopConfiguration) {
            $shopConfiguration = $this->transferValuesToShopConfiguration($shopConfiguration, $shopId);
            $this->shopConfigurationDao->save($shopConfiguration, $shopId);
        }
    }

    private function transferValuesToShopConfiguration(ShopConfiguration $shopConfiguration, $shopId): ShopConfiguration
    {
        foreach ($shopConfiguration->getModuleConfigurations() as $moduleConfiguration) {
            foreach ($moduleConfiguration->getModuleSettings() as $moduleSetting) {
                try {
                    $settingFromDatabase = $this->moduleSettingDao->get(
                        $moduleSetting->getName(),
                        $moduleConfiguration->getId(),
                        $shopId
                    );
                    $moduleSetting->setValue($settingFromDatabase->getValue());
                } catch (EntryDoesNotExistDaoException $exception) {
                    // if setting doesn't exist in the database no value should be transfered.
                }
            }
        }

        return $shopConfiguration;
    }
}
