<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\OxidEshopUpdateComponent\ThemeTemplate\Transformer;

use OxidEsales\OxidEshopUpdateComponent\ThemeSettings\ThemeSettingRenameMap;
use OxidEsales\OxidEshopUpdateComponent\ThemeTemplate\Reference\ThemeSettingReferenceInterface;

readonly class GetThemeSettingTransformer implements GetThemeSettingTransformerInterface
{
    public function __construct(private ThemeSettingReferenceInterface $themeSettingReference)
    {
    }

    public function transform(string $templateContent, array $settings): string
    {
        return $this->themeSettingReference->replace(
            $templateContent,
            function (string $originalName, string $quote) use ($settings): string {
                $newName = ThemeSettingRenameMap::getNewName($originalName);
                $getter = $this->getterForSetting($settings[$newName] ?? $settings[$originalName] ?? []);

                return "oViewConf.getThemeSettings().$getter($quote$newName$quote)";
            }
        );
    }

    private function getterForSetting(array $setting): string
    {
        return match ($setting['type'] ?? '') {
            'bool' => 'getBoolean',
            'arr', 'aarr' => 'getCollection',
            'num' => str_contains((string) ($setting['value'] ?? ''), '.') ? 'getFloat' : 'getInteger',
            default => 'getString',
        };
    }
}
