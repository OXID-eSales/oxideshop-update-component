<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\OxidEshopUpdateComponent\Tests\Unit\ThemeTemplate\Reference;

use OxidEsales\OxidEshopUpdateComponent\ThemeTemplate\Reference\ThemeSettingReference;
use PHPUnit\Framework\TestCase;

class ThemeSettingReferenceTest extends TestCase
{
    private ThemeSettingReference $themeSettingReference;

    protected function setUp(): void
    {
        $this->themeSettingReference = new ThemeSettingReference();
    }

    public function testNamesReturnsDistinctReferencedSettings(): void
    {
        $content = "{{ oViewConf.getViewThemeParam('logoFile') }}"
            . "{% if oViewConf.getViewThemeParam(\"showWishlist\") %}"
            . "{{ oViewConf.getViewThemeParam('logoFile') }}";

        $names = $this->themeSettingReference->names($content);

        sort($names);
        $this->assertSame(['logoFile', 'showWishlist'], $names);
    }

    public function testNamesReturnsEmptyWhenNoReferencePresent(): void
    {
        $this->assertSame([], $this->themeSettingReference->names('{{ oViewConf.getActiveShopId() }}'));
    }

    public function testReplaceReceivesNameAndQuoteForEachReference(): void
    {
        $content = "{{ oViewConf.getViewThemeParam('logoFile') }}"
            . '{{ oViewConf.getViewThemeParam("showWishlist") }}';

        $result = $this->themeSettingReference->replace(
            $content,
            static fn (string $name, string $quote): string => "setting($quote$name$quote)"
        );

        $this->assertSame("{{ setting('logoFile') }}{{ setting(\"showWishlist\") }}", $result);
    }

    public function testReplaceLeavesUnrelatedContentUntouched(): void
    {
        $content = '{{ oViewConf.getActiveShopId() }}';

        $this->assertSame(
            $content,
            $this->themeSettingReference->replace($content, static fn (string $name, string $quote): string => 'x')
        );
    }
}
