<?php declare(strict_types=1);
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\OxidEshopUpdateComponent\Module\Service;

use OxidEsales\Eshop\Core\Registry;
use OxidEsales\EshopCommunity\Internal\Module\Configuration\Dao\ShopConfigurationDaoInterface;
use OxidEsales\EshopCommunity\Internal\Module\Configuration\DataObject\ModuleConfiguration;

/**
 * @internal
 */
class ActiveModuleStateTransferringService implements ActiveModuleStateTransferringServiceInterface
{
    /**
     * @var ShopConfigurationDaoInterface
     */
    private $shopConfigurationDao;

    public function __construct(ShopConfigurationDaoInterface $shopConfigurationDao)
    {
        $this->shopConfigurationDao = $shopConfigurationDao;
    }

    public function transferAlreadyActiveModuleStateToProjectConfiguration(): void
    {
        $this->setAlreadyActiveModulesToConfiguredInProjectConfiguration();
    }

    private function setAlreadyActiveModulesToConfiguredInProjectConfiguration(): void
    {
        foreach ($this->shopConfigurationDao->getAll() as $shopId => $shopConfiguration) {
            foreach ($shopConfiguration->getModuleConfigurations() as $moduleConfiguration) {
                if ($this->isActive($moduleConfiguration)) {
                    $moduleConfiguration->setConfigured(true);
                }
            }

            $this->shopConfigurationDao->save($shopConfiguration, $shopId);
        }
    }

    private function isActive(ModuleConfiguration $moduleConfiguration): bool
    {
        return !$this->isInDisabledList($moduleConfiguration->getId())
            && (!$moduleConfiguration->hasClassExtensions() || $this->hasClassExtensionsInDatabase($moduleConfiguration));
    }

    private function isInDisabledList(string $moduleId): bool
    {
        return in_array($moduleId, (array) Registry::getConfig()->getConfigParam('aDisabledModules'));
    }

    private function hasClassExtensionsInDatabase(ModuleConfiguration $moduleConfiguration): bool
    {
        $chainFromDatabase = Registry::getConfig()->getModulesWithExtendedClass();

        foreach ($moduleConfiguration->getClassExtensions() as $classExtension) {
            if (
                array_key_exists($classExtension->getShopClassName(), $chainFromDatabase)
                && in_array(
                    $classExtension->getModuleExtensionClassName(),
                    $chainFromDatabase[$classExtension->getShopClassName()]
                )
            ) {
                return true;
            }
        }

        return false;
    }
}
