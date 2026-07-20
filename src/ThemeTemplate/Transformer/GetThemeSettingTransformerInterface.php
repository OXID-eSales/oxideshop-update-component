<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\OxidEshopUpdateComponent\ThemeTemplate\Transformer;

interface GetThemeSettingTransformerInterface
{
    public function transform(string $templateContent, array $settings): string;
}
