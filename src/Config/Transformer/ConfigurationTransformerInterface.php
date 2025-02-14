<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\OxidEshopUpdateComponent\Config\Transformer;

interface ConfigurationTransformerInterface
{
    public function transformToEnvConfig(array $config): array;
    public function transformToParameterConfig(array $config): array;
}
