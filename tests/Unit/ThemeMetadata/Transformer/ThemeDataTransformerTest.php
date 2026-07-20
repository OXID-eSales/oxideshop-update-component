<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\OxidEshopUpdateComponent\Tests\Unit\ThemeMetadata\Transformer;

use OxidEsales\OxidEshopUpdateComponent\ThemeMetadata\Transformer\ThemeDataTransformer;
use PHPUnit\Framework\TestCase;

class ThemeDataTransformerTest extends TestCase
{
    private ThemeDataTransformer $transformer;

    protected function setUp(): void
    {
        $this->transformer = new ThemeDataTransformer();
    }

    public function testToMetadataKeepsOnlyKnownMetadataKeys(): void
    {
        $metadata = $this->transformer->toMetadata([
            'id' => 'apex',
            'version' => '1.0.0',
            'title' => 'Apex',
            'description' => 'A theme',
            'thumbnail' => 'thumb.png',
            'author' => 'OXID',
            'parentTheme' => 'base',
            'parentVersions' => ['1.0'],
            'settings' => [['name' => 'x', 'type' => 'str', 'value' => 'y']],
            'unknown' => 'dropped',
        ]);

        $this->assertSame(
            [
                'id' => 'apex',
                'version' => '1.0.0',
                'title' => 'Apex',
                'description' => 'A theme',
                'thumbnail' => 'thumb.png',
                'author' => 'OXID',
                'parentTheme' => 'base',
                'parentVersions' => ['1.0'],
            ],
            $metadata
        );
    }

    public function testToMetadataOmitsMissingKeys(): void
    {
        $this->assertSame(['id' => 'apex'], $this->transformer->toMetadata(['id' => 'apex']));
    }

    public function testToSettingsConfigurationKeysSettingsByName(): void
    {
        $result = $this->transformer->toSettingsConfiguration([
            'settings' => [
                ['name' => 'logoFile', 'type' => 'str', 'value' => 'logo.svg', 'group' => 'logo'],
            ],
        ]);

        $this->assertSame(
            ['themeSettings' => ['logoFile' => ['type' => 'str', 'value' => 'logo.svg', 'group' => 'logo']]],
            $result
        );
    }

    public function testToSettingsConfigurationCastsBooleanValues(): void
    {
        $result = $this->transformer->toSettingsConfiguration([
            'settings' => [['name' => 'showWishlist', 'type' => 'bool', 'value' => 1, 'group' => 'features']],
        ]);

        $this->assertTrue($result['themeSettings']['showWishlist']['value']);
    }

    public function testToSettingsConfigurationDefaultsGroupToEmptyString(): void
    {
        $result = $this->transformer->toSettingsConfiguration([
            'settings' => [['name' => 'logoFile', 'type' => 'str', 'value' => 'logo.svg']],
        ]);

        $this->assertSame('', $result['themeSettings']['logoFile']['group']);
    }

    public function testToSettingsConfigurationCastsPositionToInteger(): void
    {
        $result = $this->transformer->toSettingsConfiguration([
            'settings' => [['name' => 'showManufacturer', 'type' => 'bool', 'value' => 1, 'group' => 'startpage', 'position' => '30']],
        ]);

        $this->assertSame(30, $result['themeSettings']['showManufacturer']['position']);
    }

    public function testToSettingsConfigurationSplitsPipeConstraintsIntoArray(): void
    {
        $result = $this->transformer->toSettingsConfiguration([
            'settings' => [['name' => 'zoomType', 'type' => 'select', 'value' => 'a', 'constraints' => 'a|b|c']],
        ]);

        $this->assertSame(['a', 'b', 'c'], $result['themeSettings']['zoomType']['constraints']);
    }

    public function testToSettingsConfigurationKeepsArrayConstraints(): void
    {
        $result = $this->transformer->toSettingsConfiguration([
            'settings' => [['name' => 'zoomType', 'type' => 'select', 'value' => 'a', 'constraints' => ['a', 'b']]],
        ]);

        $this->assertSame(['a', 'b'], $result['themeSettings']['zoomType']['constraints']);
    }

    public function testToSettingsConfigurationReturnsEmptyWhenNoSettings(): void
    {
        $this->assertSame(['themeSettings' => []], $this->transformer->toSettingsConfiguration([]));
    }
}
