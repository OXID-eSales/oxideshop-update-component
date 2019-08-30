<?php declare(strict_types=1);
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\OxidEshopUpdateComponent\Tests\Integration\Module\Service;

use OxidEsales\EshopCommunity\Internal\Common\Exception\EntryDoesNotExistDaoException;
use OxidEsales\EshopCommunity\Internal\Module\Configuration\Dao\ShopConfigurationDaoInterface;
use OxidEsales\EshopCommunity\Internal\Module\Configuration\DataObject\ModuleConfiguration\SmartyPluginDirectory;
use OxidEsales\EshopCommunity\Internal\Module\Configuration\DataObject\ModuleConfiguration\Template;
use OxidEsales\EshopCommunity\Internal\Module\Configuration\DataObject\ModuleConfiguration\TemplateBlock;
use OxidEsales\EshopCommunity\Internal\Module\Configuration\DataObject\ShopConfiguration;
use OxidEsales\EshopCommunity\Internal\Module\Setting\Setting;
use OxidEsales\EshopCommunity\Internal\Module\Configuration\DataObject\ModuleConfiguration\Controller;
use OxidEsales\EshopCommunity\Internal\Module\Setting\SettingDaoInterface;
use OxidEsales\EshopCommunity\Internal\Module\TemplateExtension\TemplateBlockExtensionDao;
use OxidEsales\EshopCommunity\Tests\Integration\Internal\ContainerTrait;
use OxidEsales\OxidEshopUpdateComponent\Module\Service\ModuleDataDeletionServiceInterface;
use PHPUnit\Framework\TestCase;

final class ModuleSettingsTransferringServiceTest extends TestCase
{
    use ContainerTrait;

    public function testDelete(): void
    {
        $this->prepareTestSettingsInDatabase();
        $this->prepareTestProjectConfiguration();

        /** @var ModuleDataDeletionServiceInterface $moduleSettingsTransferringService */
        $moduleSettingsTransferringService = $this->get(ModuleDataDeletionServiceInterface::class);
        $moduleSettingsTransferringService->deleteModuleDataFromDatabase();

        /** @var SettingDaoInterface $dao */
        $dao = $this->get(SettingDaoInterface::class);

        $this->expectException(EntryDoesNotExistDaoException::class);
        $dao->get('settingFromDatabase', 'first', 1);
        $dao->get('settingFromDatabase', 'second', 2);

        $dao->get('settingFromDatabase', 'third', 1);
    }

    private function prepareTestSettingsInDatabase(): void
    {
        $controller1 = new Controller('idOfController1', 'namespaceOfController1');

        $template1 = new Template('keyOfTemplate1', 'pathOfTemplate1');

        $templateBlock1 = new TemplateBlock(
            'pathOfTemplateBlock1',
            'blockNameOfTemplateBlock1',
            'moduleTemplatePathOfTemplateBlock1'
        );

        $smartyPluginDirectory1 = new SmartyPluginDirectory('directoryOfSmartyPluginDirectory');

        $setting1 = new Setting();
        $setting1
            ->setName('settingFromDatabase')
            ->setType('bool')
            ->setValue(true)
            ->setShopId(1)
            ->setModuleId('first');


        $settingDao = $this->get(SettingDaoInterface::class);
        $dao->save($setting1);

        $templateBlockDao = $this->get(TemplateBlockExtensionDao::class);
    }

    private function prepareTestProjectConfiguration(): void
    {
        $firstModule = new ModuleConfiguration();
        $firstModule
            ->setId('first')
            ->setPath('some');

        $secondModule = new ModuleConfiguration();
        $secondModule
            ->setId('second')
            ->setPath('some');

        $shopConfiguration1 = new ShopConfiguration();
        $shopConfiguration1->addModuleConfiguration($firstModule);

        $shopConfiguration2 = new ShopConfiguration();
        $shopConfiguration2->addModuleConfiguration($secondModule);

        $dao = $this->get(ShopConfigurationDaoInterface::class);
        $dao->save($shopConfiguration1, 1);
        $dao->save($shopConfiguration2, 2);
    }
}
