<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\OxidEshopUpdateComponent\Tests\Unit\ThemeMetadata\Migration;

use OxidEsales\OxidEshopUpdateComponent\ThemeMetadata\Exception\ThemeFileNotFoundException;
use OxidEsales\OxidEshopUpdateComponent\ThemeMetadata\Installer\BootstrapThemeConfigurationInstallerInterface;
use OxidEsales\OxidEshopUpdateComponent\ThemeMetadata\Migration\ThemeFileMigrator;
use OxidEsales\OxidEshopUpdateComponent\ThemeMetadata\Reader\ThemeFileReader;
use OxidEsales\OxidEshopUpdateComponent\ThemeMetadata\Transformer\ThemeDataTransformer;
use OxidEsales\OxidEshopUpdateComponent\ThemeMetadata\Writer\YamlFileWriter;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Path;
use Symfony\Component\Yaml\Yaml;

class ThemeFileMigratorTest extends TestCase
{
    private string $themeDirectory;

    protected function setUp(): void
    {
        $this->themeDirectory = sys_get_temp_dir() . '/theme-metadata-migrator-test-' . uniqid();

        (new Filesystem())->mirror(__DIR__ . '/../Fixtures/testTheme', $this->themeDirectory);
    }

    protected function tearDown(): void
    {
        (new Filesystem())->remove($this->themeDirectory);
    }

    public function testMigrateWritesThemeMetadataFile(): void
    {
        $this->createMigrator()->migrate($this->themeDirectory);

        $metadata = Yaml::parseFile(Path::join($this->themeDirectory, 'metadata.yaml'));

        $this->assertSame('testTheme', $metadata['id']);
        $this->assertSame('Test Theme', $metadata['title']);
        $this->assertSame('Theme for migration tests', $metadata['description']);
        $this->assertSame('theme.jpg', $metadata['thumbnail']);
        $this->assertSame('1.0.0', $metadata['version']);
        $this->assertSame('Partner Author', $metadata['author']);
        $this->assertSame('apex', $metadata['parentTheme']);
        $this->assertSame(['1.0'], $metadata['parentVersions']);
        $this->assertArrayNotHasKey('settings', $metadata);
    }

    public function testMigrateWritesThemeSettingsConfigurationFile(): void
    {
        $this->createMigrator()->migrate($this->themeDirectory);

        $settings = Yaml::parseFile(Path::join($this->themeDirectory, 'config.yaml'))['themeSettings'];

        $this->assertTrue($settings['blShowSomething']['value']);
        $this->assertSame('bool', $settings['blShowSomething']['type']);
        $this->assertSame('display', $settings['blShowSomething']['group']);

        $this->assertSame('partnerValue', $settings['sPartnerCustomSetting']['value']);
        $this->assertSame(5, $settings['sPartnerCustomSetting']['position']);

        $this->assertSame('grid', $settings['sListDisplayType']['value']);
        $this->assertSame(['grid', 'line', 'infogrid'], $settings['sListDisplayType']['constraints']);

        $this->assertSame(['one', 'two'], $settings['aMultiValues']['value']);
    }

    public function testMigrateThrowsForDirectoryWithoutThemeFile(): void
    {
        $this->expectException(ThemeFileNotFoundException::class);

        $this->createMigrator()->migrate(sys_get_temp_dir());
    }

    public function testMigrateInstallsThemeConfigurationForTheThemeDirectory(): void
    {
        $installer = $this->createMock(BootstrapThemeConfigurationInstallerInterface::class);
        $installer->expects($this->once())->method('install')->with($this->themeDirectory);

        $this->createMigrator($installer)->migrate($this->themeDirectory);
    }

    private function createMigrator(?BootstrapThemeConfigurationInstallerInterface $installer = null): ThemeFileMigrator
    {
        return new ThemeFileMigrator(
            new ThemeFileReader(),
            new ThemeDataTransformer(),
            new YamlFileWriter(),
            $installer ?? $this->createStub(BootstrapThemeConfigurationInstallerInterface::class)
        );
    }
}
