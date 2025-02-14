<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\OxidEshopUpdateComponent\Config\Writer;

interface ConfigurationWriterInterface
{
    public function writeEnvConfig(array $config): void;
    public function writeParameterConfig(array $config): void;
}
