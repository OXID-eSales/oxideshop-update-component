<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\OxidEshopUpdateComponent\ThemeTemplate\Builder;

readonly class ThemeSettingDefinition
{
    public function __construct(
        public string $name,
        public string $type,
        public mixed $value,
        public string $group,
        public array $constraints,
    ) {
    }
}
