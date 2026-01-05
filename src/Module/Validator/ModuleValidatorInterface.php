<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\OxidEshopUpdateComponent\Module\Validator;

interface ModuleValidatorInterface
{
    public function validate(string $modulePath): void;
}
