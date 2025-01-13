<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\OxidEshopUpdateComponent\Template\Migration;

interface TemplateFilterMigratorInterface
{
    public function migrate(string $templateDirectory): void;
}
