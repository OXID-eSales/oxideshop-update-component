<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\OxidEshopUpdateComponent\Module\Process;

interface ProcessRunnerInterface
{
    public function run(array $command): void;
}
