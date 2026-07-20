<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\OxidEshopUpdateComponent\ThemeTemplate\Reader;

interface ReferencedThemeSettingReaderInterface
{
    public function read(string $themeDirectory): array;
}
