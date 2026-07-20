<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\OxidEshopUpdateComponent\ThemeTemplate\Configuration;

interface ThemeSettingsConfigFileInterface
{
    public function getSettingNames(string $themeDirectory): array;

    public function getGroupNames(string $themeDirectory): array;

    public function addSetting(
        string $themeDirectory,
        string $name,
        string $type,
        mixed $value,
        string $group,
        array $constraints = [],
    ): void;
}
