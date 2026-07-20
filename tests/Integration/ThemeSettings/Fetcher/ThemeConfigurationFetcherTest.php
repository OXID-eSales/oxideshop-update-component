<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\OxidEshopUpdateComponent\Tests\Integration\ThemeSettings\Fetcher;

use OxidEsales\EshopCommunity\Internal\Framework\Database\QueryBuilderFactoryInterface;
use OxidEsales\EshopCommunity\Tests\Integration\IntegrationTestCase;
use OxidEsales\OxidEshopUpdateComponent\ThemeSettings\Fetcher\ThemeConfigurationFetcherInterface;

final class ThemeConfigurationFetcherTest extends IntegrationTestCase
{
    private const SHOP_ID = 1;

    public function testFetchThemeSettingsGroupsByThemeAndDecodesValues(): void
    {
        $this->insertConfigValue('str', 'sLogoFile', 'logo.svg', 'theme:apex');
        $this->insertConfigValue('bool', 'blShowWishlist', '1', 'theme:apex');
        $this->insertConfigValue('aarr', 'aImageSizes', serialize(['oxpic1' => '800*600']), 'theme:child');

        $settingsByTheme = $this->fetcher()->fetchThemeSettings(self::SHOP_ID);

        $apex = $this->indexByName($settingsByTheme['apex']);
        $this->assertSame('logo.svg', $apex['sLogoFile']['value']);
        $this->assertTrue($apex['blShowWishlist']['value']);

        $child = $this->indexByName($settingsByTheme['child']);
        $this->assertSame(['oxpic1' => '800*600'], $child['aImageSizes']['value']);
        $this->assertSame('aarr', $child['aImageSizes']['type']);
    }

    public function testFetchActiveThemeIdPrefersCustomThemeOverTheme(): void
    {
        $this->insertConfigValue('str', 'sTheme', 'apex');
        $this->insertConfigValue('str', 'sCustomTheme', 'child');

        $this->assertSame('child', $this->fetcher()->fetchActiveThemeId(self::SHOP_ID));
    }

    public function testFetchActiveThemeIdFallsBackToThemeWhenCustomThemeEmpty(): void
    {
        $this->insertConfigValue('str', 'sTheme', 'apex');
        $this->insertConfigValue('str', 'sCustomTheme', '');

        $this->assertSame('apex', $this->fetcher()->fetchActiveThemeId(self::SHOP_ID));
    }

    public function testFetchActiveThemeIdReturnsEmptyStringWhenNoActiveThemeConfigured(): void
    {
        $this->assertSame('', $this->fetcher()->fetchActiveThemeId(self::SHOP_ID));
    }

    private function fetcher(): ThemeConfigurationFetcherInterface
    {
        return $this->get(ThemeConfigurationFetcherInterface::class);
    }

    private function indexByName(array $settings): array
    {
        $indexed = [];

        foreach ($settings as $setting) {
            $indexed[$setting['name']] = $setting;
        }

        return $indexed;
    }

    private function insertConfigValue(string $type, string $name, string $value, string $module = ''): void
    {
        $this->get(QueryBuilderFactoryInterface::class)->create()
            ->delete('oxconfig')
            ->where('oxshopid = :shopId')
            ->andWhere('oxmodule = :module')
            ->andWhere('oxvarname = :name')
            ->setParameter('shopId', self::SHOP_ID)
            ->setParameter('module', $module)
            ->setParameter('name', $name)
            ->executeStatement();

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
                'id' => uniqid('fetchertest', true),
                'shopId' => self::SHOP_ID,
                'module' => $module,
                'name' => $name,
                'type' => $type,
                'value' => $value,
            ])
            ->executeStatement();
    }
}
