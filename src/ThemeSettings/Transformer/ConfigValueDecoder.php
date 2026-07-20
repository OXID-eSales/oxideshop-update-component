<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\OxidEshopUpdateComponent\ThemeSettings\Transformer;

readonly class ConfigValueDecoder implements ConfigValueDecoderInterface
{
    public function decode(string $type, string $value): mixed
    {
        return match ($type) {
            'arr', 'aarr' => unserialize($value, ['allowed_classes' => false]),
            'bool' => $value === 'true' || $value === '1',
            default => $value,
        };
    }
}
