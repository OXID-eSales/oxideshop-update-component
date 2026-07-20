<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\OxidEshopUpdateComponent\Tests\Integration\ThemeSettings\Remover;

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
                $queryBuilder->setParameter($name, $value, \Doctrine\DBAL\ArrayParameterType::STRING);
            } else {
                $queryBuilder->setParameter($name, $value);
            }
        }

        return (int) $queryBuilder->executeQuery()->fetchOne();
    }
}
