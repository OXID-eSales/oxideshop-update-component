<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\OxidEshopUpdateComponent\ThemeMetadata\Transformer;

readonly class ThemeDataTransformer implements ThemeDataTransformerInterface
{
    private const METADATA_KEYS = [
        'id',
        'version',
        'title',
        'description',
        'thumbnail',
        'author',
        'parentTheme',
        'parentVersions',
    ];

    private const TYPE_BOOLEAN = 'bool';

    public function toMetadata(array $themeData): array
    {
        $metadata = [];

        foreach (self::METADATA_KEYS as $key) {
            if (isset($themeData[$key])) {
                $metadata[$key] = $themeData[$key];
            }
        }

        return $metadata;
    }

    public function toSettingsConfiguration(array $themeData): array
    {
        $themeSettings = [];

        foreach ($themeData['settings'] ?? [] as $setting) {
            $themeSettings[$setting['name']] = $this->toSettingEntry($setting);
        }

        return ['themeSettings' => $themeSettings];
    }

    private function toSettingEntry(array $setting): array
    {
        $entry = [
            'type' => $setting['type'],
            'value' => $this->toSettingValue($setting),
            'group' => $setting['group'] ?? '',
        ];

        if (isset($setting['position'])) {
            $entry['position'] = (int) $setting['position'];
        }

        if (isset($setting['constraints'])) {
            $entry['constraints'] = $this->toConstraints($setting['constraints']);
        }

        return $entry;
    }

    private function toSettingValue(array $setting): mixed
    {
        if ($setting['type'] === self::TYPE_BOOLEAN) {
            return (bool) $setting['value'];
        }

        return $setting['value'];
    }

    private function toConstraints(mixed $constraints): array
    {
        return is_array($constraints) ? array_values($constraints) : explode('|', (string) $constraints);
    }
}
