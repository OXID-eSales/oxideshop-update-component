<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\OxidEshopUpdateComponent\Tests\Unit\ThemeTemplate\Migration;

use OxidEsales\OxidEshopUpdateComponent\ThemeTemplate\Migration\ThemeTemplateMigrator;
use OxidEsales\OxidEshopUpdateComponent\ThemeTemplate\Reference\ThemeSettingReference;
use OxidEsales\OxidEshopUpdateComponent\ThemeTemplate\Transformer\GetThemeSettingTransformer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Path;

class ThemeTemplateMigratorTest extends TestCase
{
    private string $themeDirectory;

    protected function setUp(): void
    {
        $this->themeDirectory = sys_get_temp_dir() . '/theme-template-migrator-test-' . uniqid();

        $filesystem = new Filesystem();
        $filesystem->dumpFile(
            Path::join($this->themeDirectory, 'config.yaml'),
            "themeSettings:\n  blShowWishlist:\n    type: bool\n  sLogoFile:\n    type: str\n"
            . "  productsPerPage:\n    type: num\n    value: '20'\n"
        );
        $filesystem->dumpFile(
            Path::join($this->themeDirectory, 'tpl', 'page.html.twig'),
            "{% if oViewConf.getViewThemeParam('blShowWishlist') %}"
            . "{{ oViewConf.getViewThemeParam('sLogoFile') }}"
            . "{{ oViewConf.getViewThemeParam('productsPerPage') }}{% endif %}"
        );
        $filesystem->dumpFile(
            Path::join($this->themeDirectory, 'tpl', 'plain.html.twig'),
            '{{ oViewConf.getActiveShopId() }}'
        );
    }

    protected function tearDown(): void
    {
        (new Filesystem())->remove($this->themeDirectory);
    }

    public function testMigrateRewritesThemeParamCallsWithMatchingGetters(): void
    {
        (new ThemeTemplateMigrator(new GetThemeSettingTransformer(new ThemeSettingReference())))
            ->migrate($this->themeDirectory);

        $this->assertSame(
            "{% if oViewConf.getThemeSettings().getBoolean('blShowWishlist') %}"
            . "{{ oViewConf.getThemeSettings().getString('sLogoFile') }}"
            . "{{ oViewConf.getThemeSettings().getInteger('productsPerPage') }}{% endif %}",
            file_get_contents(Path::join($this->themeDirectory, 'tpl', 'page.html.twig'))
        );
    }

    public function testMigrateLeavesTemplatesWithoutThemeParamsUntouched(): void
    {
        (new ThemeTemplateMigrator(new GetThemeSettingTransformer(new ThemeSettingReference())))
            ->migrate($this->themeDirectory);

        $this->assertSame(
            '{{ oViewConf.getActiveShopId() }}',
            file_get_contents(Path::join($this->themeDirectory, 'tpl', 'plain.html.twig'))
        );
    }
}
