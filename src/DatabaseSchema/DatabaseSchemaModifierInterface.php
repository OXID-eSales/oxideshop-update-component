<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\OxidEshopUpdateComponent\DatabaseSchema;

interface DatabaseSchemaModifierInterface
{
    public function updateDatabaseSchema(): void;
}
