<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\OxidEshopUpdateComponent\Template\Transformer;

interface TwigFilterTransformerInterface
{
    public function replace(string $templateFile): void;
}
