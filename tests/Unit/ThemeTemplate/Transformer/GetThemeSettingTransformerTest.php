<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\OxidEshopUpdateComponent\Tests\Unit\ThemeTemplate\Transformer;

use OxidEsales\OxidEshopUpdateComponent\ThemeTemplate\Reference\ThemeSettingReference;
use OxidEsales\OxidEshopUpdateComponent\ThemeTemplate\Transformer\GetThemeSettingTransformer;
use PHPUnit\Framework\TestCase;

class GetThemeSettingTransformerTest extends TestCase
{
    private GetThemeSettingTransformer $transformer;

    protected function setUp(): void
    {
        $this->transformer = new GetThemeSettingTransformer(new ThemeSettingReference());
    }

    public function testTransformRewritesStringSetting(): void
    {
        $result = $this->transformer->transform(
            "{{ oViewConf.getViewThemeParam('sLogoFile') }}",
            ['sLogoFile' => ['type' => 'str']]
        );

        $this->assertSame("{{ oViewConf.getThemeSettings().getString('sLogoFile') }}", $result);
    }

    public function testTransformUsesTypedGetterForKnownTypes(): void
    {
        $template = "{% if oViewConf.getViewThemeParam('blShowWishlist') %}"
            . "{{ oViewConf.getViewThemeParam('aNrofCatArticles') }}";

        $result = $this->transformer->transform($template, [
            'blShowWishlist' => ['type' => 'bool'],
            'aNrofCatArticles' => ['type' => 'arr'],
        ]);

        $this->assertSame(
            "{% if oViewConf.getThemeSettings().getBoolean('blShowWishlist') %}"
            . "{{ oViewConf.getThemeSettings().getCollection('aNrofCatArticles') }}",
            $result
        );
    }

    public function testTransformUsesIntegerGetterForWholeNumericSetting(): void
    {
        $result = $this->transformer->transform(
            "{{ oViewConf.getViewThemeParam('productsPerPage') }}",
            ['productsPerPage' => ['type' => 'num', 'value' => '20']]
        );

        $this->assertSame("{{ oViewConf.getThemeSettings().getInteger('productsPerPage') }}", $result);
    }

    public function testTransformUsesFloatGetterForDecimalNumericSetting(): void
    {
        $result = $this->transformer->transform(
            "{{ oViewConf.getViewThemeParam('priceFactor') }}",
            ['priceFactor' => ['type' => 'num', 'value' => '1.5']]
        );

        $this->assertSame("{{ oViewConf.getThemeSettings().getFloat('priceFactor') }}", $result);
    }

    public function testTransformFallsBackToGetStringForUnknownSetting(): void
    {
        $result = $this->transformer->transform(
            '{{ oViewConf.getViewThemeParam("sCustomPartnerSetting") }}',
            []
        );

        $this->assertSame(
            '{{ oViewConf.getThemeSettings().getString("sCustomPartnerSetting") }}',
            $result
        );
    }

    public function testTransformLeavesUnrelatedContentUntouched(): void
    {
        $template = "{{ oViewConf.getActiveShopId() }}";

        $this->assertSame($template, $this->transformer->transform($template, []));
    }
}
