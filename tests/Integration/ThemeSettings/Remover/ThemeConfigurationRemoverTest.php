<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\OxidEshopUpdateComponent\Tests\Integration\ThemeSettings\Remover;

use Doctrine\DBAL\ArrayParameterType;
use OxidEsales\EshopCommunity\Internal\Framework\Database\QueryBuilderFactoryInterface;
use OxidEsales\EshopCommunity\Tests\Integration\IntegrationTestCase;
use OxidEsales\OxidEshopUpdateComponent\ThemeSettings\Remover\ThemeConfigurationRemoverInterface;

final class ThemeConfigurationRemoverTest extends IntegrationTestCase
{
    private const SHOP_ID = 1;

    public function testRemoveDeletesThemeConfigurationDataFromDatabase(): void
    {
        $this->insertConfigValue('theme:apex', 'sLogoFile', 'logo.png');
        $this->insertConfigValue('theme:partnerTheme', 'sPartnerSetting', 'value');
        $this->insertConfigValue('', 'sTheme', 'apex');
        $this->insertConfigValue('', 'sCustomTheme', 'apex');

        $this->get(ThemeConfigurationRemoverInterface::class)->remove();

        $this->assertSame(0, $this->countConfigValues('oxmodule LIKE :theme', ['theme' => 'theme:%']));
        $this->assertSame(0, $this->countConfigValues('oxvarname IN (:names)', ['names' => ['sTheme', 'sCustomTheme']]));
    }

    public function testRemoveDeletesThemeSettingDisplayConfiguration(): void
    {
        $this->insertDisplayConfiguration('theme:apex', 'sLogoFile');
        $this->insertDisplayConfiguration('module:testModule', 'testSetting');

        $this->get(ThemeConfigurationRemoverInterface::class)->remove();

        $this->assertSame(0, $this->countDisplayConfigurations('theme:apex'));
        $this->assertSame(1, $this->countDisplayConfigurations('module:testModule'));
    }

    public function testRemoveKeepsNonThemeConfigurationData(): void
    {
        $this->insertConfigValue('theme:apex', 'sLogoFile', 'logo.png');
        $this->insertConfigValue('', 'sShopName', 'My Shop');

        $this->get(ThemeConfigurationRemoverInterface::class)->remove();

        $this->assertSame(1, $this->countConfigValues('oxvarname = :name', ['name' => 'sShopName']));
    }

    private function insertConfigValue(string $module, string $name, string $value): void
    {
        $this->get(QueryBuilderFactoryInterface::class)->create()
            ->insert('oxconfig')
            ->values([
                'oxid' => ':id',
                'oxshopid' => ':shopId',
                'oxmodule' => ':module',
                'oxvarname' => ':name',
                'oxvartype' => ':type',
                'oxvarvalue' => ':value',
            ])
            ->setParameters([
                'id' => uniqid('removertest', true),
                'shopId' => self::SHOP_ID,
                'module' => $module,
                'name' => $name,
                'type' => 'str',
                'value' => $value,
            ])
            ->executeStatement();
    }

    private function insertDisplayConfiguration(string $module, string $name): void
    {
        $this->get(QueryBuilderFactoryInterface::class)->create()
            ->insert('oxconfigdisplay')
            ->values([
                'oxid' => ':id',
                'oxcfgmodule' => ':module',
                'oxcfgvarname' => ':name',
            ])
            ->setParameters([
                'id' => substr(md5(uniqid('removertest', true)), 0, 32),
                'module' => $module,
                'name' => $name,
            ])
            ->executeStatement();
    }

    private function countDisplayConfigurations(string $module): int
    {
        return (int) $this->get(QueryBuilderFactoryInterface::class)->create()
            ->select('count(*)')
            ->from('oxconfigdisplay')
            ->where('oxcfgmodule = :module')
            ->setParameter('module', $module)
            ->executeQuery()
            ->fetchOne();
    }

    private function countConfigValues(string $condition, array $parameters): int
    {
        $queryBuilder = $this->get(QueryBuilderFactoryInterface::class)->create()
            ->select('count(*)')
            ->from('oxconfig')
            ->where('oxshopid = :shopId')
            ->andWhere($condition)
            ->setParameter('shopId', self::SHOP_ID);

        foreach ($parameters as $name => $value) {
            if (is_array($value)) {
                $queryBuilder->setParameter($name, $value, ArrayParameterType::STRING);
            } else {
                $queryBuilder->setParameter($name, $value);
            }
        }

        return (int) $queryBuilder->executeQuery()->fetchOne();
    }
}
