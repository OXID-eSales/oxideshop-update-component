<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\OxidEshopUpdateComponent\ThemeTemplate\Resolver;

use OxidEsales\OxidEshopUpdateComponent\ThemeTemplate\Builder\InteractiveThemeSettingBuilderInterface;
use OxidEsales\OxidEshopUpdateComponent\ThemeTemplate\Configuration\ThemeSettingsConfigFileInterface;
use OxidEsales\OxidEshopUpdateComponent\ThemeTemplate\Reader\ReferencedThemeSettingReaderInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

readonly class UndeclaredThemeSettingResolver implements UndeclaredThemeSettingResolverInterface
{
    private const ACTION_ADD = 'Add it to config.yaml';
    private const ACTION_REPLACE = 'Replace as-is';

    public function __construct(
        private ReferencedThemeSettingReaderInterface $referencedThemeSettingReader,
        private ThemeSettingsConfigFileInterface $themeSettingsConfigFile,
        private InteractiveThemeSettingBuilderInterface $interactiveThemeSettingBuilder,
    ) {
    }

    public function resolve(SymfonyStyle $io, bool $interactive, string $themeDirectory): void
    {
        $undeclaredSettings = array_values(array_diff(
            $this->referencedThemeSettingReader->read($themeDirectory),
            $this->themeSettingsConfigFile->getSettingNames($themeDirectory)
        ));

        if ($undeclaredSettings === []) {
            return;
        }

        if (!$interactive) {
            $io->warning(
                'These settings are used in templates but not declared in config.yaml and will throw '
                . 'at runtime unless declared: ' . implode(', ', $undeclaredSettings)
            );

            return;
        }

        foreach ($undeclaredSettings as $name) {
            $this->resolveSetting($io, $themeDirectory, $name);
        }
    }

    private function resolveSetting(SymfonyStyle $io, string $themeDirectory, string $name): void
    {
        $action = $io->choice(
            sprintf(
                "'%s' is used in templates but not declared in config.yaml; replacing it as-is throws at "
                . 'runtime until it is declared.',
                $name
            ),
            [1 => self::ACTION_ADD, 2 => self::ACTION_REPLACE],
            self::ACTION_REPLACE
        );

        if ($action !== self::ACTION_ADD) {
            return;
        }

        $definition = $this->interactiveThemeSettingBuilder->build($io, $name, $themeDirectory);
        $this->themeSettingsConfigFile->addSetting(
            $themeDirectory,
            $definition->name,
            $definition->type,
            $definition->value,
            $definition->group,
            $definition->constraints
        );
        $io->text(
            sprintf("Declared '%s' (%s) in the '%s' group of config.yaml.", $name, $definition->type, $definition->group)
        );
    }
}
