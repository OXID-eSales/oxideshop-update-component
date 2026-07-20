<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\OxidEshopUpdateComponent\Tests\Integration\ThemeSettings\Migration;

use OxidEsales\EshopCommunity\Internal\Framework\Database\QueryBuilderFactoryInterface;
use OxidEsales\EshopCommunity\Internal\Framework\Theme\Configuration\Dao\ThemeConfigurationDaoInterface;
use OxidEsales\EshopCommunity\Internal\Framework\Theme\Configuration\DataObject\ThemeConfiguration;
use OxidEsales\EshopCommunity\Internal\Framework\Theme\Setting\Setting;
use OxidEsales\EshopCommunity\Tests\ContainerTrait;
use OxidEsales\EshopCommunity\Tests\DatabaseTrait;
use OxidEsales\EshopCommunity\Tests\Integration\IntegrationTestCase;
use OxidEsales\OxidEshopUpdateComponent\ThemeSettings\Migration\ThemeSettingsMigratorInterface;

final class ThemeSettingsMigratorTest extends IntegrationTestCase
{
    private const THEME_ID = 'testTheme';
    private const SHOP_ID = 1;

    public function testMigrateUpdatesValuesOfInstalledThemeSettings(): void
    {
        $this->installThemeConfiguration();
        $this->insertThemeConfigValue('str', 'sKnownSetting', 'valueFromDatabase');
        $this->insertThemeConfigValue('bool', 'blKnownFlag', '1');

        $this->get(ThemeSettingsMigratorInterface::class)->migrate();

        $configuration = $this->getThemeConfiguration();
        $this->assertSame('valueFromDatabase', $configuration->getSettingByName('sKnownSetting')->getValue());
        $this->assertTrue($configuration->getSettingByName('blKnownFlag')->getValue());
        $this->assertSame('display', $configuration->getSettingByName('sKnownSetting')->getGroupName());
    }

    public function testMigrateAddsCustomSettingsMissingInThemeConfiguration(): void
    {
        $this->installThemeConfiguration();
        $this->insertThemeConfigValue('aarr', 'aPartnerCustomSetting', serialize(['key' => 'value']));

        $this->get(ThemeSettingsMigratorInterface::class)->migrate();

        $customSetting = $this->getThemeConfiguration()->getSettingByName('aPartnerCustomSetting');
        $this->assertSame(['key' => 'value'], $customSetting->getValue());
        $this->assertSame('aarr', $customSetting->getType());
    }

    public function testMigrateSkipsAndReportsThemesWithoutExistingConfiguration(): void
    {
        $this->insertThemeConfigValue('str', 'sPartnerSetting', 'partnerValue', 'partnerTheme');

        $result = $this->get(ThemeSettingsMigratorInterface::class)->migrate();

        $this->assertContains('partnerTheme', $result->themesWithoutConfiguration);
        $this->assertFalse($this->get(ThemeConfigurationDaoInterface::class)->exists('partnerTheme', self::SHOP_ID));
    }

    public function testMigrateActivatesThemeConfiguredAsActiveInDatabase(): void
    {
        $this->installThemeConfiguration();
        $this->insertConfigValue('str', 'sTheme', self::THEME_ID);

        $this->get(ThemeSettingsMigratorInterface::class)->migrate();

        $this->assertTrue($this->getThemeConfiguration()->isActivated());
    }

    public function testMigratePrefersCustomThemeAsActiveTheme(): void
    {
        $this->installThemeConfiguration();
        $this->installThemeConfiguration('customChildTheme');
        $this->insertConfigValue('str', 'sTheme', self::THEME_ID);
        $this->insertConfigValue('str', 'sCustomTheme', 'customChildTheme');

        $this->get(ThemeSettingsMigratorInterface::class)->migrate();

        $dao = $this->get(ThemeConfigurationDaoInterface::class);
        $this->assertTrue($dao->get('customChildTheme', self::SHOP_ID)->isActivated());
        $this->assertFalse($dao->get(self::THEME_ID, self::SHOP_ID)->isActivated());
    }

    public function testMigrateWithoutThemeDataChangesNothing(): void
    {
        $this->installThemeConfiguration();

        $this->get(ThemeSettingsMigratorInterface::class)->migrate();

        $this->assertSame(
            'defaultValue',
            $this->getThemeConfiguration()->getSettingByName('sKnownSetting')->getValue()
        );
    }

    private function installThemeConfiguration(string $themeId = self::THEME_ID): void
    {
        $configuration = (new ThemeConfiguration())
            ->setId($themeId)
            ->setSource('source/Application/views/' . $themeId)
            ->addThemeSetting(
                (new Setting())->setName('sKnownSetting')->setType('str')->setValue('defaultValue')->setGroupName('display')
            )
            ->addThemeSetting(
                (new Setting())->setName('blKnownFlag')->setType('bool')->setValue(false)->setGroupName('display')
            );

        $this->get(ThemeConfigurationDaoInterface::class)->save($configuration, self::SHOP_ID);
    }

    private function insertThemeConfigValue(
        string $type,
        string $name,
        string $value,
        string $themeId = self::THEME_ID,
    ): void {
        $this->insertConfigValue($type, $name, $value, 'theme:' . $themeId);
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
                'id' => uniqid('migrationtest', true),
                'shopId' => self::SHOP_ID,
                'module' => $module,
                'name' => $name,
                'type' => $type,
                'value' => $value,
            ])
            ->executeStatement();
    }

    private function getThemeConfiguration(): ThemeConfiguration
    {
        return $this->get(ThemeConfigurationDaoInterface::class)->get(self::THEME_ID, self::SHOP_ID);
    }
}
