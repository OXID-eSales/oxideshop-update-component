<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\OxidEshopUpdateComponent\ThemeTemplate\Migration;

interface ThemeTemplateMigratorInterface
{
    public function migrate(string $themeDirectory): void;
}
