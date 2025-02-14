<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\OxidEshopUpdateComponent\Module\Validator;

use OxidEsales\OxidEshopUpdateComponent\Module\Exception\ModuleNotFoundException;

class ModuleValidator implements ModuleValidatorInterface
{
    public function validate(string $modulePath): void
    {
        if (!is_dir($modulePath)) {
            throw new ModuleNotFoundException(
                sprintf('Module directory not found: %s', $modulePath)
            );
        }
    }
}
