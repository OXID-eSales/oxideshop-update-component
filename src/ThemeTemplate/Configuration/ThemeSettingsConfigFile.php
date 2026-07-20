<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\OxidEshopUpdateComponent\ThemeTemplate\Configuration;

use OxidEsales\OxidEshopUpdateComponent\ThemeMetadata\Writer\YamlFileWriterInterface;
use Symfony\Component\Filesystem\Path;
use Symfony\Component\Yaml\Yaml;

readonly class ThemeSettingsConfigFile implements ThemeSettingsConfigFileInterface
{
    private const FILE_NAME = 'config.yaml';

    public function __construct(private YamlFileWriterInterface $yamlFileWriter)
    {
    }

    public function getSettingNames(string $themeDirectory): array
    {
        return array_keys($this->readConfiguration($themeDirectory)['themeSettings'] ?? []);
    }

    public function getGroupNames(string $themeDirectory): array
    {
        $groups = [];

        foreach ($this->readConfiguration($themeDirectory)['themeSettings'] ?? [] as $setting) {
            $group = (string) ($setting['group'] ?? '');

            if ($group !== '') {
                $groups[$group] = true;
            }
        }

        return array_keys($groups);
    }

    public function addSetting(
        string $themeDirectory,
        string $name,
        string $type,
        mixed $value,
        string $group,
        array $constraints = [],
    ): void {
        $configuration = $this->readConfiguration($themeDirectory);

        $setting = [
            'type' => $type,
            'value' => $value,
            'group' => $group,
        ];

        if ($constraints !== []) {
            $setting['constraints'] = $constraints;
        }

        $configuration['themeSettings'][$name] = $setting;

        $this->yamlFileWriter->write(Path::join($themeDirectory, self::FILE_NAME), $configuration);
    }

    private function readConfiguration(string $themeDirectory): array
    {
        $settingsFile = Path::join($themeDirectory, self::FILE_NAME);

        if (!is_file($settingsFile)) {
            return [];
        }

        return Yaml::parseFile($settingsFile) ?? [];
    }
}
