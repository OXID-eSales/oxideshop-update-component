<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\OxidEshopUpdateComponent\Config\Transformer;

interface ConfigurationTransformerInterface
{
    public function transformToEnvConfig(array $config): array;
    public function transformToParameterConfig(array $config): array;
}
