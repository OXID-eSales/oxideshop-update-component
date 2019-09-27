<?php declare(strict_types=1);
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\OxidEshopUpdateComponent\Module\Service;

use OxidEsales\EshopCommunity\Internal\Framework\Config\Dao\ShopConfigurationSettingDaoInterface;
use OxidEsales\EshopCommunity\Internal\Framework\Config\DataObject\ShopConfigurationSetting;
use OxidEsales\EshopCommunity\Internal\Framework\Dao\EntryDoesNotExistDaoException;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Configuration\Dao\ShopConfigurationDaoInterface;
use OxidEsales\OxidEshopUpdateComponent\Module\Service\ModuleExtensionsSorting\ExtensionsSorter;
use OxidEsales\OxidEshopUpdateComponent\Adapter\ShopAdapter;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Service sorts extensions in configuration file according data in database.
 *
 * @internal
 */
class ModuleExtensionsSortingService implements ModuleExtensionsSortingServiceInterface
{
    /** @var ShopConfigurationSettingDaoInterface */
    private $shopConfigurationSettingDao;

    /**
     * @var ShopConfigurationDaoInterface
     */
    private $shopConfigurationDao;

    /**
     * @var ExtensionsSorter
     */
    private $extensionsSorter;

    /**
     * @var ShopAdapter
     */
    private $shopAdapter;

    /**
     * @var OutputInterface
     */
    private $output;

    public function __construct(
        ShopConfigurationSettingDaoInterface $shopConfigurationSettingDao,
        ShopConfigurationDaoInterface $shopConfigurationDao,
        ShopAdapter $shopAdapter,
        ExtensionsSorter $extensionsSorter,
        OutputInterface $output
    ) {
        $this->shopConfigurationSettingDao = $shopConfigurationSettingDao;
        $this->shopConfigurationDao = $shopConfigurationDao;
        $this->shopAdapter = $shopAdapter;
        $this->extensionsSorter = $extensionsSorter;
        $this->output = $output;
    }

    public function sort(): void
    {
        foreach ($this->shopConfigurationDao->getAll() as $shopId => $shopConfiguration) {
            $this->output->writeln('<info>Sort module extensions for the shop with id ' . $shopId . '</info>');
            try {
                $extensionsFromDatabase = $this->shopConfigurationSettingDao->get(
                    ShopConfigurationSetting::MODULE_CLASS_EXTENSIONS_CHAIN,
                    $shopId
                )->getValue();
                $this->sortExtensionsByShopId($extensionsFromDatabase, $shopId);
            } catch (EntryDoesNotExistDaoException $exception) {
                // if setting doesn't exist sorting shouldn't be executed.
            }
        }
    }

    private function sortExtensionsByShopId(array $extensionsFromDatabase, int $shopId): void
    {
        $shopConfiguration = $this->shopConfigurationDao->get($shopId);
        $classExtensionsChain = $shopConfiguration->getClassExtensionsChain();
        $extensionsFromFile = $classExtensionsChain->getChain();

        $extensionsFromDatabase = $this->shopAdapter->parseModuleChains($extensionsFromDatabase);
        $sortedExtensions = $this->extensionsSorter->sort($extensionsFromFile, $extensionsFromDatabase);

        $classExtensionsChain->setChain($sortedExtensions);
        $shopConfiguration->setClassExtensionsChain($classExtensionsChain);
        $this->shopConfigurationDao->save($shopConfiguration, $shopId);
    }
}
