<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\OxidEshopUpdateComponent\Config\Migration;

use OxidEsales\OxidEshopUpdateComponent\Config\ConfigurationPathProvider;
use OxidEsales\OxidEshopUpdateComponent\Config\Reader\ConfigurationReaderInterface;
use OxidEsales\OxidEshopUpdateComponent\Config\Transformer\ConfigurationTransformerInterface;
use OxidEsales\OxidEshopUpdateComponent\Config\Validator\ConfigurationValidatorInterface;
use OxidEsales\OxidEshopUpdateComponent\Config\Writer\ConfigurationWriterInterface;
use Symfony\Component\Filesystem\Filesystem;

class ConfigurationMigrator implements ConfigurationMigratorInterface
{
    public function __construct(
        private readonly ConfigurationValidatorInterface $validator,
        private readonly ConfigurationReaderInterface $reader,
        private readonly ConfigurationTransformerInterface $transformer,
        private readonly ConfigurationWriterInterface $writer,
        private readonly ConfigurationPathProvider $pathProvider,
        private readonly Filesystem $filesystem
    ) {
    }

    public function migrate(): void
    {
        $this->validator->validate();
        $this->createBackup();

        $originalConfig = $this->reader->read();

        $envConfig = $this->transformer->transformToEnvConfig($originalConfig);
        $parameterConfig = $this->transformer->transformToParameterConfig($originalConfig);

        $this->writer->writeEnvConfig($envConfig);
        $this->writer->writeParameterConfig($parameterConfig);

        $this->removeOriginalConfig();
    }

    private function createBackup(): void
    {
        $this->filesystem->copy(
            $this->pathProvider->getConfigurationFilePath(),
            $this->pathProvider->getConfigurationBackupPath()
        );
    }

    private function removeOriginalConfig(): void
    {
        $this->filesystem->remove($this->pathProvider->getConfigurationFilePath());
    }
}