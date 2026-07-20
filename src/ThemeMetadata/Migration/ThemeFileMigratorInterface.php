<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\OxidEshopUpdateComponent\ThemeMetadata\Migration;

use OxidEsales\OxidEshopUpdateComponent\ThemeMetadata\Exception\ThemeFileNotFoundException;

interface ThemeFileMigratorInterface
{
    /** @throws ThemeFileNotFoundException */
    public function migrate(string $themeDirectory): void;
}
