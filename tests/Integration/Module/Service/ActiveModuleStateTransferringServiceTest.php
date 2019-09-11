<?php declare(strict_types=1);
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\OxidEshopUpdateComponent\Tests\Integration\Module\Service;

use OxidEsales\Eshop\Core\Registry;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Configuration\Dao\ModuleConfigurationDaoInterface;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Configuration\Dao\ShopConfigurationDaoInterface;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Configuration\DataObject\ModuleConfiguration;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Configuration\DataObject\ShopConfiguration;
use OxidEsales\EshopCommunity\Tests\Integration\Internal\ContainerTrait;
use OxidEsales\OxidEshopUpdateComponent\Module\Service\ActiveModuleStateTransferringServiceInterface;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class ActiveModuleStateTransferringServiceTest extends TestCase
{
    use ContainerTrait;

    protected function setUp()
    {
        parent::setUp();
        $this->prepareTestProjectConfiguration();

        Registry::getConfig()->saveShopConfVar('arr', 'aDisabledModules', [], 1);
        Registry::getConfig()->saveShopConfVar('arr', 'aModules', [], 1);
    }

    public function testModuleNotSetToConfiguredIfInDisabledList(): void
    {
        Registry::getConfig()->saveShopConfVar('arr', 'aDisabledModules', ['withExtensions'], 1);
        Registry::getConfig()->saveShopConfVar('arr', 'aModules', ['shopClass' => 'moduleClass&otherModuleClass'], 1);

        $this->get(ActiveModuleStateTransferringServiceInterface::class)->transferAlreadyActiveModuleStateToProjectConfiguration();

        $this->assertFalse(
            $this->get(ModuleConfigurationDaoInterface::class)->get('withExtensions', 1)->isConfigured()
        );
    }

    public function testModuleNotSetToConfiguredIfNotInDisabledListAndNoModuleExtensionsInDatabase(): void
    {
        $this->get(ActiveModuleStateTransferringServiceInterface::class)->transferAlreadyActiveModuleStateToProjectConfiguration();

        $this->assertFalse(
            $this->get(ModuleConfigurationDaoInterface::class)->get('withExtensions', 1)->isConfigured()
        );
    }

    public function testModuleSetToConfiguredIfNotInDisabledListAndHasNoExtensions(): void
    {
        $this->get(ActiveModuleStateTransferringServiceInterface::class)->transferAlreadyActiveModuleStateToProjectConfiguration();

        $this->assertTrue(
            $this->get(ModuleConfigurationDaoInterface::class)->get('withoutExtensions', 1)->isConfigured()
        );
    }

    public function testModuleSetToConfiguredIfNotInDisabledListAndThereIsModuleExtensionInDatabase(): void
    {
        Registry::getConfig()->saveShopConfVar('arr', 'aModules', ['shopClass' => 'moduleClass&otherModuleClass'], 1);

        $this->get(ActiveModuleStateTransferringServiceInterface::class)->transferAlreadyActiveModuleStateToProjectConfiguration();

        $this->assertTrue(
            $this->get(ModuleConfigurationDaoInterface::class)->get('withExtensions', 1)->isConfigured()
        );
    }

    private function prepareTestProjectConfiguration(): void
    {
        $moduleWithExtensions = new ModuleConfiguration();
        $moduleWithExtensions
            ->setId('withExtensions')
            ->setPath('some')
            ->setConfigured(false)
            ->addClassExtension(new ModuleConfiguration\ClassExtension('shopClass', 'moduleClass'));

        $moduleWithoutExtensions = new ModuleConfiguration();
        $moduleWithoutExtensions
            ->setId('withoutExtensions')
            ->setPath('some')
            ->setConfigured(false);

        $shopConfiguration = new ShopConfiguration();
        $shopConfiguration->addModuleConfiguration($moduleWithExtensions);
        $shopConfiguration->addModuleConfiguration($moduleWithoutExtensions);

        $dao = $this->get(ShopConfigurationDaoInterface::class);
        $dao->save($shopConfiguration, 1);
    }
}
