<?php declare(strict_types=1);
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\OxidEshopUpdateComponent\Module\Service;

use OxidEsales\EshopCommunity\Internal\Framework\Config\Dao\ShopConfigurationSettingDaoInterface;
use OxidEsales\EshopCommunity\Internal\Framework\Config\DataObject\ShopConfigurationSetting;
use OxidEsales\EshopCommunity\Internal\Transition\Utility\ContextInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @internal
 */
class ModuleDataDeletionService implements ModuleDataDeletionServiceInterface
{
    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @var ShopConfigurationSettingDaoInterface
     */
    private $shopConfigurationSettingDao;

    /**
     * @var ContextInterface
     */
    private $context;

    /**
     * ModuleDataDeletionService constructor.
     * @param OutputInterface $output
     * @param ShopConfigurationSettingDaoInterface $shopConfigurationSettingDao
     * @param ContextInterface $context
     */
    public function __construct(
        OutputInterface $output,
        ShopConfigurationSettingDaoInterface $shopConfigurationSettingDao,
        ContextInterface $context
    ) {
        $this->output = $output;
        $this->shopConfigurationSettingDao = $shopConfigurationSettingDao;
        $this->context = $context;
    }

    public function deleteModuleDataFromDatabase(): void
    {
        foreach ($this->context->getAllShopIds() as $shopId) {
            $this->output->writeln('Deleting module data from the database for the shop with id ' . $shopId);
            foreach ($this->getSettingNamesToDelete() as $settingName) {
                $setting = new ShopConfigurationSetting();
                $setting
                    ->setName($settingName)
                    ->setShopId($shopId);

                $this->shopConfigurationSettingDao->delete($setting);
            }
        }
    }

    private function getSettingNamesToDelete(): array
    {
        return [
            ShopConfigurationSetting::MODULE_CONTROLLERS,
            ShopConfigurationSetting::MODULE_CLASS_EXTENSIONS_CHAIN,
            ShopConfigurationSetting::MODULE_CLASS_EXTENSIONS,
            ShopConfigurationSetting::MODULE_CLASSES_WITHOUT_NAMESPACES,
            ShopConfigurationSetting::MODULE_PATHS,
            ShopConfigurationSetting::MODULE_EVENTS,
            ShopConfigurationSetting::MODULE_SMARTY_PLUGIN_DIRECTORIES,
            ShopConfigurationSetting::MODULE_TEMPLATES,
            ShopConfigurationSetting::MODULE_VERSIONS,
            'aDisabledModules',
        ];
    }
}
