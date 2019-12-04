<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\OxidEshopUpdateComponent\Decoder\DataMigration;

interface ValidatorInterface
{
    public function validateIfAlreadyMigrated(string $table, string $column): void;
}
