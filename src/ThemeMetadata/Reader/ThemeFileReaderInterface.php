<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\OxidEshopUpdateComponent\ThemeMetadata\Reader;

use OxidEsales\OxidEshopUpdateComponent\ThemeMetadata\Exception\ThemeFileNotFoundException;

interface ThemeFileReaderInterface
{
    /** @throws ThemeFileNotFoundException */
    public function read(string $themeDirectory): array;
}
