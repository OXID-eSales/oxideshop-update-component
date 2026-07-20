<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\OxidEshopUpdateComponent\ThemeTemplate\Migration;

use OxidEsales\OxidEshopUpdateComponent\ThemeTemplate\Transformer\GetThemeSettingTransformerInterface;
use Symfony\Component\Filesystem\Path;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Yaml\Yaml;

readonly class ThemeTemplateMigrator implements ThemeTemplateMigratorInterface
{
    private const SETTINGS_FILE_NAME = 'config.yaml';
    private const TEMPLATE_EXTENSION = 'twig';

    public function __construct(
        private GetThemeSettingTransformerInterface $getThemeSettingTransformer
    ) {
    }

    public function migrate(string $themeDirectory): void
    {
        $settings = $this->readSettings($themeDirectory);

        foreach ($this->findTemplateFiles($themeDirectory) as $templateFile) {
            $originalContent = (string) file_get_contents($templateFile);
            $migratedContent = $this->getThemeSettingTransformer->transform($originalContent, $settings);

            if ($migratedContent !== $originalContent) {
                file_put_contents($templateFile, $migratedContent);
            }
        }
    }

    private function readSettings(string $themeDirectory): array
    {
        $settingsFile = Path::join($themeDirectory, self::SETTINGS_FILE_NAME);

        if (!is_file($settingsFile)) {
            return [];
        }

        return Yaml::parseFile($settingsFile)['themeSettings'] ?? [];
    }

    private function findTemplateFiles(string $themeDirectory): iterable
    {
        $finder = (new Finder())->files()->in($themeDirectory)->name('*.' . self::TEMPLATE_EXTENSION);

        foreach ($finder as $file) {
            yield $file->getPathname();
        }
    }
}
