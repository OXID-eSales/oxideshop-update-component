<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\OxidEshopUpdateComponent\Tests\Integration\DatabaseConfig\Migrator;

use OxidEsales\Eshop\Application\Model\Shop;
use OxidEsales\EshopCommunity\Core\Di\ContainerFacade;
use OxidEsales\EshopCommunity\Internal\Framework\Config\Dao\ShopConfigurationSettingDaoInterface;
use OxidEsales\EshopCommunity\Internal\Framework\Config\DataObject\ShopConfigurationSetting;
use OxidEsales\EshopCommunity\Internal\Framework\Config\DataObject\ShopSettingType;
use OxidEsales\EshopCommunity\Internal\Framework\Dao\EntryDoesNotExistDaoException;
use OxidEsales\EshopCommunity\Internal\Framework\DIContainer\Dao\ParameterDaoInterface;
use OxidEsales\EshopCommunity\Internal\Transition\Utility\ContextInterface;
use OxidEsales\EshopCommunity\Tests\ContainerTrait;
use OxidEsales\EshopCommunity\Tests\DatabaseTrait;
use OxidEsales\EshopCommunity\Tests\Integration\IntegrationTestCase;
use OxidEsales\EshopEnterprise\Tests\Integration\ShopSwitcherTrait;
use OxidEsales\OxidEshopUpdateComponent\DatabaseConfig\Fetcher\DatabaseConfigurationFetcherInterface;
use OxidEsales\OxidEshopUpdateComponent\DatabaseConfig\Migrator\DatabaseToContainerConfigurationMigrator;
use OxidEsales\OxidEshopUpdateComponent\DatabaseConfig\ParametersProvider\ConfigurationParametersProviderInterface;

final class DatabaseToContainerConfigurationMigratorWithCleanupTest extends IntegrationTestCase
{
    use DatabaseTrait;
    use ContainerTrait;
    use ShopSwitcherTrait;

    private DatabaseToContainerConfigurationMigrator $migrator;

    public function setUp(): void
    {
        parent::setUp();

        $this->beginTransaction();
        $this->createShops([1, 4]);
        $this->createDatabaseConfigurationParameters();

        $this->initializeMigrator();
    }

    public function tearDown(): void
    {
        parent::tearDown();
        $this->rollBackTransaction();
    }

    private function initializeMigrator(): void
    {
        $this->migrator = new DatabaseToContainerConfigurationMigrator(
            $this->get(ContextInterface::class),
            $this->get(ConfigurationParametersProviderInterface::class),
            $this->get(DatabaseConfigurationFetcherInterface::class),
            $this->get(ParameterDaoInterface::class)
        );
    }

    public function testMigrateDatabaseToContainerConfiguration(): void
    {
        $this->migrator->migrateDatabaseToContainerConfiguration();

        $this->switchShop(1);

        $this->assertTrue(ContainerFacade::getParameter('oxid_esales.enable_content_cache'));

        $this->switchShop(4);

        $this->assertTrue(ContainerFacade::getParameter('oxid_esales.enable_data_cache'));
        $this->assertFalse(ContainerFacade::getParameter('oxid_esales.enable_content_cache'));

        $this->assertConfigurationNotExists(1, 'blUseContentCaching');
        $this->assertConfigurationNotExists(1, 'blCacheActive');
        $this->assertConfigurationNotExists(4, 'blUseContentCaching');
        $this->assertConfigurationNotExists(4, 'blCacheActive');
    }

    private function createShops(array $ids): void
    {
        foreach ($ids as $id) {
            $shop = new Shop();
            $shop->setId((string) $id);
            $shop->save();
        }
    }

    private function createDatabaseConfigurationParameters(): void
    {
        $this->createShopConfiguration(1, 'blUseContentCaching', true);
        $this->createShopConfiguration(4, 'blUseContentCaching', false);
        $this->createShopConfiguration(4, 'blCacheActive', true);
    }

    private function createShopConfiguration(int $shopId, string $name, bool $value): void
    {
        $shopConfigurationSetting = new ShopConfigurationSetting();
        $shopConfigurationSetting->setShopId($shopId);
        $shopConfigurationSetting->setName($name);
        $shopConfigurationSetting->setValue($value);
        $shopConfigurationSetting->setType(ShopSettingType::BOOLEAN);
        $this->get(ShopConfigurationSettingDaoInterface::class)->save($shopConfigurationSetting);
    }

    private function assertConfigurationNotExists(int $shopId, string $key): void
    {
        $this->expectException(EntryDoesNotExistDaoException::class);
        $this->get(ShopConfigurationSettingDaoInterface::class)->get($key, $shopId);
    }
}
