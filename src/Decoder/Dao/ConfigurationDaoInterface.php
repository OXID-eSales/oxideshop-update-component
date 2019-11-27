<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\OxidEshopUpdateComponent\Decoder\Dao;

/**
 * @internal
 */
interface ConfigurationDaoInterface
{
    public function decodeValues(): void;
}
