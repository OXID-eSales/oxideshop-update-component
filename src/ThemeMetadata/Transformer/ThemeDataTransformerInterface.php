<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\OxidEshopUpdateComponent\ThemeMetadata\Transformer;

interface ThemeDataTransformerInterface
{
    public function toMetadata(array $themeData): array;

    public function toSettingsConfiguration(array $themeData): array;
}
