<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\OxidEshopUpdateComponent\Command;

use OxidEsales\OxidEshopUpdateComponent\ThemeTemplate\Migration\ThemeTemplateMigratorInterface;
use OxidEsales\OxidEshopUpdateComponent\ThemeTemplate\Resolver\UndeclaredThemeSettingResolverInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Throwable;

class MigrateThemeTemplatesCommand extends Command
{
    private string $themeDirectoryArgument = 'theme-directory';

    public function __construct(
        private readonly ThemeTemplateMigratorInterface $themeTemplateMigrator,
        private readonly UndeclaredThemeSettingResolverInterface $undeclaredThemeSettingResolver,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument(
                $this->themeDirectoryArgument,
                InputArgument::REQUIRED,
                'Path to the theme directory whose templates should be migrated.'
            )
            ->setDescription(
                'Rewrites getViewThemeParam() template calls to the typed getThemeSettings() theme setting service.'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $themeDirectory = (string) $input->getArgument($this->themeDirectoryArgument);

        try {
            $this->undeclaredThemeSettingResolver->resolve($io, $input->isInteractive(), $themeDirectory);
            $this->themeTemplateMigrator->migrate($themeDirectory);
        } catch (Throwable $exception) {
            $io->error('Theme template migration failed: ' . $exception->getMessage());

            return Command::FAILURE;
        }

        $io->success('Theme templates migrated to the getThemeSettings() theme setting service.');

        return Command::SUCCESS;
    }
}
