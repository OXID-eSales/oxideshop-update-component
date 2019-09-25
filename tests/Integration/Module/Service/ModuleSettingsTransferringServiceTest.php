<?php declare(strict_types=1);
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\OxidEshopUpdateComponent\Tests\Integration\Module\Service;

use OxidEsales\EshopCommunity\Internal\Framework\Module\Configuration\Dao\ShopConfigurationDaoInterface;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Configuration\DataObject\ModuleConfiguration;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Configuration\DataObject\ShopConfiguration;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Setting\Setting;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Setting\SettingDaoInterface;
use OxidEsales\EshopCommunity\Tests\Integration\Internal\ContainerTrait;
use OxidEsales\OxidEshopUpdateComponent\Module\Service\ModuleSettingsTransferringServiceInterface;
use PHPUnit\Framework\TestCase;

final class ModuleSettingsTransferringServiceTest extends TestCase
{
    use ContainerTrait;

    public function testTransferring(): void
    {
        $this->prepareTestSettingsInDatabase();
        $this->prepareTestProjectConfiguration();

        $moduleSettingsTransferringService = $this->get(ModuleSettingsTransferringServiceInterface::class);
        $moduleSettingsTransferringService->transferValuesFromDatabaseToProjectConfiguration();

        /** @var ShopConfigurationDaoInterface $shopConfigurationDao */
        $shopConfigurationDao = $this->get(ShopConfigurationDaoInterface::class);

        $this->assertSame(
            true,
            $shopConfigurationDao
                ->get(1)
                ->getModuleConfiguration('first')
                ->getModuleSetting('settingFromDatabase')
                ->getValue()
        );

        $this->assertSame(
            'valueFromDatabase',
            $shopConfigurationDao
                ->get(2)
                ->getModuleConfiguration('second')
                ->getModuleSetting('settingFromDatabase')
                ->getValue()
        );
    }

    private function prepareTestSettingsInDatabase(): void
    {
        $setting1 = new Setting();
        $setting1
            ->setName('settingFromDatabase')
            ->setType('bool')
            ->setValue(true);

        $setting2 = new Setting();
        $setting2
            ->setName('settingFromDatabase')
            ->setType('str')
            ->setValue('valueFromDatabase');

        $dao = $this->get(SettingDaoInterface::class);
        $dao->save($setting1, 'first', 1);
        $dao->save($setting2, 'second', 2);
    }

    private function prepareTestProjectConfiguration(): void
    {
        $setting1 = new Setting();
        $setting1
            ->setName('settingFromDatabase')
            ->setType('bool')
            ->setValue(false);

        $setting2 = new Setting();
        $setting2
            ->setName('settingFromDatabase')
            ->setType('str')
            ->setValue('valueFromFileToOverwrite');

        $setting3 = new Setting();
        $setting3
            ->setName('settingNotInDatabase')
            ->setType('str')
            ->setValue('valueShouldStay');

        $firstModule = new ModuleConfiguration();
        $firstModule
            ->setId('first')
            ->setPath('some')
            ->addModuleSetting($setting1)
            ->addModuleSetting($setting3);

        $secondModule = new ModuleConfiguration();
        $secondModule
            ->setId('second')
            ->setPath('some')
            ->addModuleSetting($setting2);


        $shopConfiguration1 = new ShopConfiguration();
        $shopConfiguration1->addModuleConfiguration($firstModule);

        $shopConfiguration2 = new ShopConfiguration();
        $shopConfiguration2->addModuleConfiguration($secondModule);

        $dao = $this->get(ShopConfigurationDaoInterface::class);
        $dao->save($shopConfiguration1, 1);
        $dao->save($shopConfiguration2, 2);
    }
}
