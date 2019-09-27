<?php declare(strict_types=1);
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\OxidEshopUpdateComponent\Tests\Integration\Module\Service;

use Doctrine\DBAL\Query\QueryBuilder;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\EshopCommunity\Internal\Framework\Config\Dao\ShopConfigurationSettingDaoInterface;
use OxidEsales\EshopCommunity\Internal\Framework\Config\DataObject\ShopConfigurationSetting;
use OxidEsales\EshopCommunity\Internal\Framework\Database\QueryBuilderFactoryInterface;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Configuration\Dao\ShopConfigurationDaoInterface;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Configuration\DataObject\ClassExtensionsChain;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Configuration\DataObject\ModuleConfiguration\ClassExtension;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Configuration\DataObject\ShopConfiguration;
use OxidEsales\EshopCommunity\Tests\Integration\Internal\ContainerTrait;
use OxidEsales\OxidEshopUpdateComponent\Module\Service\ModuleExtensionsSortingService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tests\Fixtures\DummyOutput;

final class ModuleExtensionsSortingServiceTest extends TestCase
{
    use ContainerTrait;

    public function testSorting(): void
    {
        $this->prepareExtensionsInDatabase();
        $this->prepareTestProjectConfiguration();

        $this->makeExtensionsSorter()->sort();

        /** @var ShopConfigurationDaoInterface $shopConfigurationDao */
        $shopConfigurationDao = $this->get(ShopConfigurationDaoInterface::class);

        $expectedSortedExtensionsInShop1 = [
            'OxidEshopClass1' => [
                'Module1\Class',
                'Module2\Class',
            ],
            'OxidEshopClass2' => [
                'Module3\Class'
            ],
        ];
        $expectedSortedExtensionsInShop2 = [
            'OxidEshopClass1' => [
                'Module3\Class'
            ],
        ];

        $this->assertSame(
            $expectedSortedExtensionsInShop1,
            $shopConfigurationDao
                ->get(1)->getClassExtensionsChain()
                ->getChain()
        );

        $this->assertSame(
            $expectedSortedExtensionsInShop2,
            $shopConfigurationDao
                ->get(2)->getClassExtensionsChain()
                ->getChain()
        );
    }

    public function testWhenNoDataInDatabase(): void
    {
        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = $this->get(QueryBuilderFactoryInterface::class)->create();
        $queryBuilder
            ->delete('oxconfig')
            ->where('oxvarname = :name')
            ->andWhere('oxshopid = :shopId')
            ->setParameters([
                'shopId'    => 1,
                'name'      => ShopConfigurationSetting::MODULE_CLASS_EXTENSIONS_CHAIN,
            ]);
        $queryBuilder->execute();

        $this->prepareTestProjectConfiguration();

        $this->makeExtensionsSorter()->sort();

        /** @var ShopConfigurationDaoInterface $shopConfigurationDao */
        $shopConfigurationDao = $this->get(ShopConfigurationDaoInterface::class);

        $expectedSortedExtensionsInShop1 = [
            'OxidEshopClass1' => [
                'Module2\Class',
                'Module1\Class',
            ],
            'OxidEshopClass2' => [
                'Module3\Class'
            ],
        ];
        $expectedSortedExtensionsInShop2 = [
            'OxidEshopClass1' => [
                'Module3\Class'
            ],
        ];

        $this->assertSame(
            $expectedSortedExtensionsInShop1,
            $shopConfigurationDao
                ->get(1)->getClassExtensionsChain()
                ->getChain()
        );

        $this->assertSame(
            $expectedSortedExtensionsInShop2,
            $shopConfigurationDao
                ->get(2)->getClassExtensionsChain()
                ->getChain()
        );
    }

    private function prepareExtensionsInDatabase(): void
    {
        $modules1 = [
            'OxidEshopClass1' => 'Module1\Class&Module2\Class',
            'OxidEshopClass2' => 'Module3\Class',
        ];

        Registry::getConfig()->saveShopConfVar(
            'aarr',
            ShopConfigurationSetting::MODULE_CLASS_EXTENSIONS_CHAIN,
            $modules1,
            1
        );

        $modules2 = [
            'OxidEshopClass1' => 'Module2\Class',
        ];
        Registry::getConfig()->saveShopConfVar(
            'aarr',
            ShopConfigurationSetting::MODULE_CLASS_EXTENSIONS_CHAIN,
            $modules2,
            2
        );
    }

    private function prepareTestProjectConfiguration(): void
    {
        $classExtension = new ClassExtensionsChain();
        $classExtension->addExtension(
            new ClassExtension(
                'OxidEshopClass1',
                'Module2\Class'
            )
        );
        $classExtension->addExtension(
            new ClassExtension(
                'OxidEshopClass2',
                'Module3\Class'
            )
        );
        $classExtension->addExtension(
            new ClassExtension(
                'OxidEshopClass1',
                'Module1\Class'
            )
        );
        $shopConfiguration1 = new ShopConfiguration();
        $shopConfiguration1->setClassExtensionsChain($classExtension);
        $dao = $this->get(ShopConfigurationDaoInterface::class);
        $dao->save($shopConfiguration1, 1);

        $classExtension2 = new ClassExtensionsChain();
        $classExtension2->addExtension(
            new ClassExtension(
                'OxidEshopClass1',
                'Module3\Class'
            )
        );
        $shopConfiguration2 = new ShopConfiguration();
        $shopConfiguration2->setClassExtensionsChain($classExtension2);

        $dao = $this->get(ShopConfigurationDaoInterface::class);
        $dao->save($shopConfiguration2, 2);
    }

    private function makeExtensionsSorter(): ModuleExtensionsSortingService
    {
        return new ModuleExtensionsSortingService(
            $this->get(ShopConfigurationSettingDaoInterface::class),
            $this->get(ShopConfigurationDaoInterface::class),
            $this->get('oxid_esales.oxid_eshop_update_component.adapter.shop_adapter'),
            $this->get('oxid_esales.oxid_eshop_update_component.service.module_extensions_sorting.extensions_sorter'),
            new DummyOutput()
        );
    }
}
