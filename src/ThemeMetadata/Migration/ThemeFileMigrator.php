<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\OxidEshopUpdateComponent\ThemeMetadata\Migration;

use OxidEsales\OxidEshopUpdateComponent\ThemeMetadata\Installer\BootstrapThemeConfigurationInstallerInterface;
use OxidEsales\OxidEshopUpdateComponent\ThemeMetadata\Reader\ThemeFileReaderInterface;
use OxidEsales\OxidEshopUpdateComponent\ThemeMetadata\Transformer\ThemeDataTransformerInterface;
use OxidEsales\OxidEshopUpdateComponent\ThemeMetadata\Writer\YamlFileWriterInterface;
use Symfony\Component\Filesystem\Path;

readonly class ThemeFileMigrator implements ThemeFileMigratorInterface
{
    private const METADATA_FILE_NAME = 'metadata.yaml';
    private const SETTINGS_FILE_NAME = 'config.yaml';

    public function __construct(
        private ThemeFileReaderInterface $themeFileReader,
        private ThemeDataTransformerInterface $themeDataTransformer,
        private YamlFileWriterInterface $yamlFileWriter,
        private BootstrapThemeConfigurationInstallerInterface $themeConfigurationInstaller,
    ) {
    }

    public function migrate(string $themeDirectory): void
    {
        $themeDirectory = Path::makeAbsolute($themeDirectory, getcwd());
        $themeData = $this->themeFileReader->read($themeDirectory);

        $this->yamlFileWriter->write(
            Path::join($themeDirectory, self::METADATA_FILE_NAME),
            $this->themeDataTransformer->toMetadata($themeData)
        );
        $this->yamlFileWriter->write(
            Path::join($themeDirectory, self::SETTINGS_FILE_NAME),
            $this->themeDataTransformer->toSettingsConfiguration($themeData)
        );

        $this->themeConfigurationInstaller->install($themeDirectory);
    }
}
