<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\OxidEshopUpdateComponent\Command;

use OxidEsales\OxidEshopUpdateComponent\ThemeSettings\Migration\ThemeSettingsMigratorInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Throwable;

class MigrateThemeConfigurationCommand extends Command
{
    public function __construct(
        private readonly ThemeSettingsMigratorInterface $themeSettingsMigrator
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription(
            'Migrates theme settings and the active theme state from the oxconfig table to the theme YAML configuration.'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $themesWithoutConfiguration = $this->themeSettingsMigrator->migrate()->themesWithoutConfiguration;
        } catch (Throwable $exception) {
            $io->error('Theme configuration migration failed: ' . $exception->getMessage());

            return Command::FAILURE;
        }

        if ($themesWithoutConfiguration !== []) {
            $io->warning(
                'Skipped settings for themes without a YAML configuration: '
                . implode(', ', $themesWithoutConfiguration)
                . '. Run oe:update:migrate-theme-metadata for these themes first, then re-run this command.'
            );
        }

        $io->success('Theme settings and active theme state successfully migrated to the YAML configuration.');

        return Command::SUCCESS;
    }
}
