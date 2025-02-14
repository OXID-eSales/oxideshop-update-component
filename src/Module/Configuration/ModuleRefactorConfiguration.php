<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\OxidEshopUpdateComponent\Module\Configuration;

class ModuleRefactorConfiguration
{
    public function __construct(
        public readonly bool $importNames = false,
        public readonly int $typeCoverageLevel = 0,
        public readonly int $deadCodeLevel = 0,
        public readonly int $codeQualityLevel = 0,
        public readonly array $preparedSets = [],
        public readonly array $customSets = []
    ) {
    }
}
