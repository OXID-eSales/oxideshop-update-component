<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\OxidEshopUpdateComponent\ThemeSettings\Migration;

readonly class ThemeSettingsMigrationResult
{
    public function __construct(public array $themesWithoutConfiguration)
    {
    }
}
