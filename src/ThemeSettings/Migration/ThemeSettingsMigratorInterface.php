<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\OxidEshopUpdateComponent\ThemeSettings\Migration;

interface ThemeSettingsMigratorInterface
{
    public function migrate(): ThemeSettingsMigrationResult;
}
