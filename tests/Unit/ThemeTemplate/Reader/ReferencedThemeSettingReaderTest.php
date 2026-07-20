<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\OxidEshopUpdateComponent\Tests\Unit\ThemeTemplate\Reader;

use OxidEsales\OxidEshopUpdateComponent\ThemeTemplate\Reader\ReferencedThemeSettingReader;
use OxidEsales\OxidEshopUpdateComponent\ThemeTemplate\Reference\ThemeSettingReference;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Path;

class ReferencedThemeSettingReaderTest extends TestCase
{
    private string $themeDirectory;

    protected function setUp(): void
    {
        $this->themeDirectory = sys_get_temp_dir() . '/referenced-theme-setting-reader-' . uniqid();
    }

    protected function tearDown(): void
    {
        (new Filesystem())->remove($this->themeDirectory);
    }

    public function testReadReturnsDistinctReferencedSettingNames(): void
    {
        $this->createTemplate('page.html.twig', "{% if oViewConf.getViewThemeParam('showWishlist') %}"
            . "{{ oViewConf.getViewThemeParam(\"logoFile\") }}{% endif %}");
        $this->createTemplate('widget.html.twig', "{{ oViewConf.getViewThemeParam('showWishlist') }}");

        $referenced = (new ReferencedThemeSettingReader(new ThemeSettingReference()))->read($this->themeDirectory);

        sort($referenced);
        $this->assertSame(['logoFile', 'showWishlist'], $referenced);
    }

    public function testReadReturnsEmptyWhenNoThemeParamsUsed(): void
    {
        $this->createTemplate('plain.html.twig', '{{ oViewConf.getActiveShopId() }}');

        $this->assertSame([], (new ReferencedThemeSettingReader(new ThemeSettingReference()))->read($this->themeDirectory));
    }

    private function createTemplate(string $name, string $content): void
    {
        (new Filesystem())->dumpFile(Path::join($this->themeDirectory, 'tpl', $name), $content);
    }
}
