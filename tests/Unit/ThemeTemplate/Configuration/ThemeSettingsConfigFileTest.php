<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\OxidEshopUpdateComponent\Tests\Unit\ThemeTemplate\Configuration;

use OxidEsales\OxidEshopUpdateComponent\ThemeMetadata\Writer\YamlFileWriter;
use OxidEsales\OxidEshopUpdateComponent\ThemeTemplate\Configuration\ThemeSettingsConfigFile;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Path;
use Symfony\Component\Yaml\Yaml;

class ThemeSettingsConfigFileTest extends TestCase
{
    private string $themeDirectory;
    private ThemeSettingsConfigFile $configFile;

    protected function setUp(): void
    {
        $this->themeDirectory = sys_get_temp_dir() . '/theme-settings-config-file-' . uniqid();
        $this->configFile = new ThemeSettingsConfigFile(new YamlFileWriter());
    }

    protected function tearDown(): void
    {
        (new Filesystem())->remove($this->themeDirectory);
    }

    public function testGetSettingNamesReturnsDeclaredSettings(): void
    {
        $this->writeConfig("themeSettings:\n  logoFile:\n    type: str\n  showWishlist:\n    type: bool\n");

        $names = $this->configFile->getSettingNames($this->themeDirectory);

        sort($names);
        $this->assertSame(['logoFile', 'showWishlist'], $names);
    }

    public function testGetSettingNamesReturnsEmptyWhenNoConfigFile(): void
    {
        $this->assertSame([], $this->configFile->getSettingNames($this->themeDirectory));
    }

    public function testGetGroupNamesReturnsDistinctNonEmptyGroups(): void
    {
        $this->writeConfig(
            "themeSettings:\n  logoFile:\n    group: logo\n  logoWidth:\n    group: logo\n"
            . "  showWishlist:\n    group: features\n  orphan:\n    group: ''\n"
        );

        $groups = $this->configFile->getGroupNames($this->themeDirectory);

        sort($groups);
        $this->assertSame(['features', 'logo'], $groups);
    }

    public function testAddSettingAppendsSettingWithGivenTypeValueAndGroup(): void
    {
        $this->writeConfig("themeSettings:\n  logoFile:\n    type: str\n    value: logo.svg\n    group: logo\n");

        $this->configFile->addSetting($this->themeDirectory, 'showWishlist', 'bool', true, 'features');
        $this->configFile->addSetting($this->themeDirectory, 'logoWidth', 'str', '200', 'logo');

        $settings = Yaml::parseFile(Path::join($this->themeDirectory, 'config.yaml'))['themeSettings'];
        $this->assertSame('logo.svg', $settings['logoFile']['value']);
        $this->assertSame('bool', $settings['showWishlist']['type']);
        $this->assertTrue($settings['showWishlist']['value']);
        $this->assertSame('features', $settings['showWishlist']['group']);
        $this->assertSame('200', $settings['logoWidth']['value']);
        $this->assertSame('logo', $settings['logoWidth']['group']);
    }

    public function testAddSettingWritesConstraintsForSelectAndOmitsThemOtherwise(): void
    {
        $this->configFile->addSetting($this->themeDirectory, 'zoomType', 'select', 'modal', 'pdp', ['no_zoom', 'modal']);
        $this->configFile->addSetting($this->themeDirectory, 'logoFile', 'str', 'logo.svg', 'logo');

        $settings = Yaml::parseFile(Path::join($this->themeDirectory, 'config.yaml'))['themeSettings'];
        $this->assertSame(['no_zoom', 'modal'], $settings['zoomType']['constraints']);
        $this->assertArrayNotHasKey('constraints', $settings['logoFile']);
    }

    private function writeConfig(string $content): void
    {
        (new Filesystem())->dumpFile(Path::join($this->themeDirectory, 'config.yaml'), $content);
    }
}
