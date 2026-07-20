<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\OxidEshopUpdateComponent\Tests\Unit\ThemeTemplate\Command;

use OxidEsales\OxidEshopUpdateComponent\Command\MigrateThemeTemplatesCommand;
use OxidEsales\OxidEshopUpdateComponent\ThemeMetadata\Writer\YamlFileWriter;
use OxidEsales\OxidEshopUpdateComponent\ThemeTemplate\Builder\InteractiveThemeSettingBuilder;
use OxidEsales\OxidEshopUpdateComponent\ThemeTemplate\Configuration\ThemeSettingsConfigFile;
use OxidEsales\OxidEshopUpdateComponent\ThemeTemplate\Migration\ThemeTemplateMigrator;
use OxidEsales\OxidEshopUpdateComponent\ThemeTemplate\Reader\ReferencedThemeSettingReader;
use OxidEsales\OxidEshopUpdateComponent\ThemeTemplate\Reference\ThemeSettingReference;
use OxidEsales\OxidEshopUpdateComponent\ThemeTemplate\Resolver\UndeclaredThemeSettingResolver;
use OxidEsales\OxidEshopUpdateComponent\ThemeTemplate\Transformer\GetThemeSettingTransformer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Path;
use Symfony\Component\Yaml\Yaml;

class MigrateThemeTemplatesCommandTest extends TestCase
{
    private string $themeDirectory;

    protected function setUp(): void
    {
        $this->themeDirectory = sys_get_temp_dir() . '/migrate-theme-templates-command-' . uniqid();
        (new Filesystem())->dumpFile(
            Path::join($this->themeDirectory, 'config.yaml'),
            "themeSettings:\n  logoFile:\n    type: str\n"
        );
        (new Filesystem())->dumpFile(
            Path::join($this->themeDirectory, 'tpl', 'page.html.twig'),
            "{{ oViewConf.getViewThemeParam('logoFile') }}"
            . "{% if oViewConf.getViewThemeParam('newestWidth') %}x{% endif %}"
        );
    }

    protected function tearDown(): void
    {
        (new Filesystem())->remove($this->themeDirectory);
    }

    public function testAddsUndeclaredSettingToConfigThenMigratesWithMatchingGetter(): void
    {
        $tester = new CommandTester($this->createCommand());
        $tester->setInputs(['Add it to config.yaml', 'bool', 'true', 'features']);

        $tester->execute(['theme-directory' => $this->themeDirectory]);

        $settings = Yaml::parseFile(Path::join($this->themeDirectory, 'config.yaml'))['themeSettings'];
        $this->assertSame('bool', $settings['newestWidth']['type']);
        $this->assertTrue($settings['newestWidth']['value']);
        $this->assertSame('features', $settings['newestWidth']['group']);
        $this->assertSame(
            "{{ oViewConf.getThemeSettings().getString('logoFile') }}"
            . "{% if oViewConf.getThemeSettings().getBoolean('newestWidth') %}x{% endif %}",
            file_get_contents(Path::join($this->themeDirectory, 'tpl', 'page.html.twig'))
        );
    }

    public function testAddsUndeclaredSelectSettingWithConstraints(): void
    {
        $tester = new CommandTester($this->createCommand());
        $tester->setInputs(['Add it to config.yaml', 'select', 'line,grid', 'grid', 'display']);

        $tester->execute(['theme-directory' => $this->themeDirectory]);

        $setting = Yaml::parseFile(Path::join($this->themeDirectory, 'config.yaml'))['themeSettings']['newestWidth'];
        $this->assertSame('select', $setting['type']);
        $this->assertSame('grid', $setting['value']);
        $this->assertSame(['line', 'grid'], $setting['constraints']);
        $this->assertSame('display', $setting['group']);
    }

    public function testAddsUndeclaredAssociativeArraySettingFromKeyValuePairs(): void
    {
        $tester = new CommandTester($this->createCommand());
        $tester->setInputs(['Add it to config.yaml', 'aarr', 'oxpic1:800*600, oxpic2:400*300', 'images']);

        $tester->execute(['theme-directory' => $this->themeDirectory]);

        $setting = Yaml::parseFile(Path::join($this->themeDirectory, 'config.yaml'))['themeSettings']['newestWidth'];
        $this->assertSame('aarr', $setting['type']);
        $this->assertSame(['oxpic1' => '800*600', 'oxpic2' => '400*300'], $setting['value']);
    }

    public function testNonInteractiveRunWarnsAndLeavesUndeclaredSettingAsGetString(): void
    {
        $tester = new CommandTester($this->createCommand());

        $tester->execute(
            ['theme-directory' => $this->themeDirectory],
            ['interactive' => false]
        );

        $settings = Yaml::parseFile(Path::join($this->themeDirectory, 'config.yaml'))['themeSettings'];
        $this->assertArrayNotHasKey('newestWidth', $settings);
        $this->assertStringContainsString('newestWidth', $tester->getDisplay());
        $this->assertStringContainsString(
            "getThemeSettings().getString('newestWidth')",
            file_get_contents(Path::join($this->themeDirectory, 'tpl', 'page.html.twig'))
        );
    }

    public function testAddsUndeclaredStringSetting(): void
    {
        $tester = new CommandTester($this->createCommand());
        $tester->setInputs(['Add it to config.yaml', 'str', 'my-value', 'general']);

        $tester->execute(['theme-directory' => $this->themeDirectory]);

        $setting = Yaml::parseFile(Path::join($this->themeDirectory, 'config.yaml'))['themeSettings']['newestWidth'];
        $this->assertSame('str', $setting['type']);
        $this->assertSame('my-value', $setting['value']);
    }

    public function testAddsUndeclaredNumericSettingUsesIntegerGetter(): void
    {
        $tester = new CommandTester($this->createCommand());
        $tester->setInputs(['Add it to config.yaml', 'num', '20', 'display']);

        $tester->execute(['theme-directory' => $this->themeDirectory]);

        $setting = Yaml::parseFile(Path::join($this->themeDirectory, 'config.yaml'))['themeSettings']['newestWidth'];
        $this->assertSame(20, $setting['value']);
        $this->assertStringContainsString(
            "getThemeSettings().getInteger('newestWidth')",
            file_get_contents(Path::join($this->themeDirectory, 'tpl', 'page.html.twig'))
        );
    }

    public function testAddsUndeclaredArraySettingFromCommaSeparatedValues(): void
    {
        $tester = new CommandTester($this->createCommand());
        $tester->setInputs(['Add it to config.yaml', 'arr', 'a, b', 'features']);

        $tester->execute(['theme-directory' => $this->themeDirectory]);

        $setting = Yaml::parseFile(Path::join($this->themeDirectory, 'config.yaml'))['themeSettings']['newestWidth'];
        $this->assertSame(['a', 'b'], $setting['value']);
    }

    public function testReplaceAsIsLeavesSettingUndeclaredAndRewritesAsGetString(): void
    {
        $tester = new CommandTester($this->createCommand());
        $tester->setInputs(['Replace as-is']);

        $tester->execute(['theme-directory' => $this->themeDirectory]);

        $settings = Yaml::parseFile(Path::join($this->themeDirectory, 'config.yaml'))['themeSettings'];
        $this->assertArrayNotHasKey('newestWidth', $settings);
        $this->assertStringContainsString(
            "getThemeSettings().getString('newestWidth')",
            file_get_contents(Path::join($this->themeDirectory, 'tpl', 'page.html.twig'))
        );
    }

    private function createCommand(): MigrateThemeTemplatesCommand
    {
        $themeSettingReference = new ThemeSettingReference();
        $themeSettingsConfigFile = new ThemeSettingsConfigFile(new YamlFileWriter());

        return new MigrateThemeTemplatesCommand(
            new ThemeTemplateMigrator(new GetThemeSettingTransformer($themeSettingReference)),
            new UndeclaredThemeSettingResolver(
                new ReferencedThemeSettingReader($themeSettingReference),
                $themeSettingsConfigFile,
                new InteractiveThemeSettingBuilder($themeSettingsConfigFile)
            )
        );
    }
}
