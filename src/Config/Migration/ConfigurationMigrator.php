<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\OxidEshopUpdateComponent\Config\Migration;

use OxidEsales\OxidEshopUpdateComponent\Config\Reader\ConfigurationReaderInterface;
use OxidEsales\OxidEshopUpdateComponent\Config\Transformer\ConfigurationTransformerInterface;
use OxidEsales\OxidEshopUpdateComponent\Config\Validator\ConfigurationValidatorInterface;
use OxidEsales\OxidEshopUpdateComponent\Config\Writer\ConfigurationWriterInterface;

class ConfigurationMigrator implements ConfigurationMigratorInterface
{
    public function __construct(
        private readonly ConfigurationValidatorInterface $validator,
        private readonly ConfigurationReaderInterface $reader,
        private readonly ConfigurationTransformerInterface $transformer,
        private readonly ConfigurationWriterInterface $writer
    ) {
    }

    public function migrate(): void
    {
        $this->validator->validate();

        $originalConfig = $this->reader->read();

        $envConfig = $this->transformer->transformToEnvConfig($originalConfig);
        $parameterConfig = $this->transformer->transformToParameterConfig($originalConfig);

        $this->writer->writeEnvConfig($envConfig);
        $this->writer->writeParameterConfig($parameterConfig);
    }
}
