<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\OxidEshopUpdateComponent\ThemeTemplate\Reference;

readonly class ThemeSettingReference implements ThemeSettingReferenceInterface
{
    private const PATTERN = '/oViewConf\.getViewThemeParam\(\s*([\'"])([A-Za-z0-9_]+)\1\s*\)/';

    public function names(string $content): array
    {
        $names = [];

        if (preg_match_all(self::PATTERN, $content, $matches)) {
            foreach ($matches[2] as $name) {
                $names[$name] = true;
            }
        }

        return array_keys($names);
    }

    public function replace(string $content, callable $replace): string
    {
        return preg_replace_callback(
            self::PATTERN,
            static fn (array $matches): string => $replace($matches[2], $matches[1]),
            $content
        );
    }
}
