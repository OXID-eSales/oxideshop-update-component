<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\OxidEshopUpdateComponent\ThemeTemplate\Reference;

interface ThemeSettingReferenceInterface
{
    public function names(string $content): array;

    public function replace(string $content, callable $replace): string;
}
