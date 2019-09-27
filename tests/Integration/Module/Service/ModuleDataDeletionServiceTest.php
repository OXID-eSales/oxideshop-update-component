<?php declare(strict_types=1);
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\OxidEshopUpdateComponent\Tests\Integration\Module\Service;

use OxidEsales\Eshop\Core\DatabaseProvider;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\EshopCommunity\Internal\Framework\Config\Dao\ShopConfigurationSettingDaoInterface;
use OxidEsales\EshopCommunity\Internal\Framework\Config\DataObject\ShopConfigurationSetting;
use OxidEsales\EshopCommunity\Internal\Framework\Dao\EntryDoesNotExistDaoException;
use OxidEsales\EshopCommunity\Tests\Integration\Internal\ContainerTrait;
use OxidEsales\OxidEshopUpdateComponent\Module\Service\ModuleDataDeletionServiceInterface;
use PHPUnit\Framework\TestCase;

final class ModuleDataDeletionServiceTest extends TestCase
{
    use ContainerTrait;

    public function testDelete(): void
    {
        Registry::getConfig()->saveShopConfVar(
            'aarr',
            ShopConfigurationSetting::MODULE_CONTROLLERS,
            ['testModuleId' => ['controller']],
            1
        );

        $this->get(ModuleDataDeletionServiceInterface::class)->deleteModuleDataFromDatabase();

        $this->expectException(EntryDoesNotExistDaoException::class);
        $this
            ->get(ShopConfigurationSettingDaoInterface::class)
            ->get(ShopConfigurationSetting::MODULE_CONTROLLERS, 1);
    }

    public function testDeleteInSubShop(): void
    {
        $this->createSecondShop();

        Registry::getConfig()->saveShopConfVar(
            'aarr',
            ShopConfigurationSetting::MODULE_CONTROLLERS,
            ['testModuleId' => ['controller']],
            2
        );

        $this->get(ModuleDataDeletionServiceInterface::class)->deleteModuleDataFromDatabase();

        $this->expectException(EntryDoesNotExistDaoException::class);
        $this
            ->get(ShopConfigurationSettingDaoInterface::class)
            ->get(ShopConfigurationSetting::MODULE_CONTROLLERS, 2);
    }

    private function createSecondShop(): void
    {
        DatabaseProvider::getDb()->execute("INSERT INTO `oxshops` (OXID) VALUES (2)");
    }
}
