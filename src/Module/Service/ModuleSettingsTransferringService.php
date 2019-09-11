<?php declare(strict_types=1);
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\OxidEshopUpdateComponent\Module\Service;

use OxidEsales\EshopCommunity\Internal\Framework\Dao\EntryDoesNotExistDaoException;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Configuration\Dao\ShopConfigurationDaoInterface;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Configuration\DataObject\ShopConfiguration;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Setting\SettingDaoInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @internal
 */
class ModuleSettingsTransferringService implements ModuleSettingsTransferringServiceInterface
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
     * @var OutputInterface
     */
    private $output;

    public function __construct(
        ShopConfigurationDaoInterface $shopConfigurationDao,
        SettingDaoInterface $moduleSettingDao,
        OutputInterface $output
    ) {
        $this->shopConfigurationDao = $shopConfigurationDao;
        $this->moduleSettingDao = $moduleSettingDao;
        $this->output = $output;
    }

    public function transferValuesFromDatabaseToProjectConfiguration(): void
    {
        foreach ($this->shopConfigurationDao->getAll() as $shopId => $shopConfiguration) {
            $this->output->writeln('<info>Transferring module settings for the shop with id ' . $shopId . '</info>');
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
