<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\OxidEshopUpdateComponent\DatabaseSchema\Updater;

interface DatabaseUpdaterInterface
{
    public function update(): void;
}
