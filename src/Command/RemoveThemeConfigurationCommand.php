<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\OxidEshopUpdateComponent\Command;

use OxidEsales\OxidEshopUpdateComponent\ThemeSettings\Remover\ThemeConfigurationRemoverInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Throwable;

class RemoveThemeConfigurationCommand extends Command
{
    private string $forceOption = 'force';

    public function __construct(
        private readonly ThemeConfigurationRemoverInterface $themeConfigurationRemover
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption($this->forceOption, null, InputOption::VALUE_NONE, 'Skip the confirmation prompt.')
            ->setDescription('Removes all theme configuration data from the oxconfig table.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->warning(
            'This permanently removes all theme settings and the active theme state from the oxconfig table. '
            . 'Make sure the theme configuration has been migrated to the YAML configuration first.'
        );

        if (!$input->getOption($this->forceOption) && !$io->confirm('Do you want to continue?', false)) {
            $io->note('Aborted. No data was removed.');

            return Command::SUCCESS;
        }

        try {
            $this->themeConfigurationRemover->remove();
        } catch (Throwable $exception) {
            $io->error('Removing theme configuration failed: ' . $exception->getMessage());

            return Command::FAILURE;
        }

        $io->success('Theme configuration data has been removed from the oxconfig table.');

        return Command::SUCCESS;
    }
}
