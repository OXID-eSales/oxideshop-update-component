<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\OxidEshopUpdateComponent\ThemeSettings\Transformer;

interface ConfigValueDecoderInterface
{
    public function decode(string $type, string $value): mixed;
}
