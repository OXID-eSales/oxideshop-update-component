<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\OxidEshopUpdateComponent\ThemeSettings\Fetcher;

interface ThemeConfigurationFetcherInterface
{
    public function fetchThemeSettings(int $shopId): array;

    public function fetchActiveThemeId(int $shopId): string;
}
