<?php declare(strict_types=1);
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\OxidEshopUpdateComponent\Module\Service;

use OxidEsales\Eshop\Core\Registry;
use OxidEsales\EshopCommunity\Internal\Framework\Config\Dao\ShopConfigurationSettingDaoInterface;
use OxidEsales\EshopCommunity\Internal\Framework\Dao\EntryDoesNotExistDaoException;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Configuration\Dao\ShopConfigurationDaoInterface;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Configuration\DataObject\ModuleConfiguration;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @internal
 */
class ActiveModuleStateTransferringService implements ActiveModuleStateTransferringServiceInterface
{
    /**
     * @var ShopConfigurationDaoInterface
     */
    private $shopConfigurationDao;

    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @var ShopConfigurationSettingDaoInterface
     */
    private $shopConfigurationSettingDao;

    public function __construct(
        ShopConfigurationDaoInterface $shopConfigurationDao,
        OutputInterface $output,
        ShopConfigurationSettingDaoInterface $shopConfigurationSettingDao
    ) {
        $this->shopConfigurationDao = $shopConfigurationDao;
        $this->output = $output;
        $this->shopConfigurationSettingDao = $shopConfigurationSettingDao;
    }

    public function transferAlreadyActiveModuleStateToProjectConfiguration(): void
    {
        $this->setAlreadyActiveModulesToConfiguredInProjectConfiguration();
    }

    private function setAlreadyActiveModulesToConfiguredInProjectConfiguration(): void
    {
        $this->output->writeln('<info>Transferring modules active state</info>');
        foreach ($this->shopConfigurationDao->getAll() as $shopId => $shopConfiguration) {
            $this->output->writeln('<info>Checking active modules in the shop with id ' . $shopId . '</info>');
            foreach ($shopConfiguration->getModuleConfigurations() as $moduleConfiguration) {
                if ($this->isActive($moduleConfiguration, $shopId)) {
                    $this->output->writeln('<info>' . $moduleConfiguration->getId() . ' module has active state</info>');
                    $moduleConfiguration->setConfigured(true);
                }
            }

            $this->shopConfigurationDao->save($shopConfiguration, $shopId);
        }
    }

    private function isActive(ModuleConfiguration $moduleConfiguration, int $shopId): bool
    {
        return !$this->isInDisabledList($moduleConfiguration->getId(), $shopId)
            && (!$moduleConfiguration->hasClassExtensions() || $this->hasClassExtensionsInDatabase($moduleConfiguration, $shopId));
    }

    private function isInDisabledList(string $moduleId, int $shopId): bool
    {
        return in_array($moduleId, $this->getDisabledList($shopId));
    }

    private function hasClassExtensionsInDatabase(ModuleConfiguration $moduleConfiguration, int $shopId): bool
    {
        $chainFromDatabase = $this->getExtensionsFromDatabase($shopId);

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

    private function getDisabledList(int $shopId): array
    {
        try {
            return $this
                ->shopConfigurationSettingDao
                ->get('aDisabledModules', $shopId)
                ->getValue();
        } catch (EntryDoesNotExistDaoException $exception) {
            return [];
        }
    }

    private function getExtensionsFromDatabase(int $shopId): array
    {
        try {
            $extensions = $this
                ->shopConfigurationSettingDao
                ->get('aModules', $shopId)
                ->getValue();

            return Registry::getConfig()->parseModuleChains($extensions);
        } catch (EntryDoesNotExistDaoException $exception) {
            return [];
        }
    }
}
