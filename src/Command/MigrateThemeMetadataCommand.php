<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\OxidEshopUpdateComponent\Command;

use OxidEsales\OxidEshopUpdateComponent\ThemeMetadata\Migration\ThemeFileMigratorInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Throwable;

class MigrateThemeMetadataCommand extends Command
{
    private string $themeDirectoryArgument = 'theme-directory';

    public function __construct(
        private readonly ThemeFileMigratorInterface $themeFileMigrator
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument(
                $this->themeDirectoryArgument,
                InputArgument::REQUIRED,
                'Path to the theme directory containing the theme.php file.'
            )
            ->setDescription('Migrates a theme.php file to metadata.yaml and config.yaml.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $this->themeFileMigrator->migrate($input->getArgument($this->themeDirectoryArgument));
        } catch (Throwable $exception) {
            $io->error('Theme metadata migration failed: ' . $exception->getMessage());

            return Command::FAILURE;
        }

        $io->success('Theme metadata successfully migrated to metadata.yaml and config.yaml.');

        return Command::SUCCESS;
    }
}
