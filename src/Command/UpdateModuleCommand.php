<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\OxidEshopUpdateComponent\Command;

use OxidEsales\OxidEshopUpdateComponent\Module\Configuration\ModuleRefactorConfiguration;
use OxidEsales\OxidEshopUpdateComponent\Module\Update\ModuleUpdaterInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class UpdateModuleCommand extends Command
{
    public function __construct(
        private readonly ModuleUpdaterInterface $moduleUpdater
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('oe:update:update-module')
            ->setDescription('Updates module code to be compatible with OXID eShop 8')
            ->addArgument(
                'module-path',
                InputArgument::REQUIRED,
                'Path to the module directory'
            )
            ->addOption(
                'config',
                'c',
                InputOption::VALUE_NONE,
                'Update config parameter calls'
            )
            ->addOption(
                'facts',
                'f',
                InputOption::VALUE_NONE,
                'Update Facts and Edition related code'
            )
            ->addOption(
                'transaction',
                't',
                InputOption::VALUE_NONE,
                'Update transaction related code'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $modulePath = $input->getArgument('module-path');

        $io->title(sprintf('Starting module update for module at "%s"', $modulePath));

        $customSets = [];
        foreach (['config', 'facts', 'transaction'] as $option) {
            if ($input->getOption($option)) {
                $customSets[$option] = true;
            }
        }

        if (empty($customSets)) {
            $io->note('No update rules selected. Use -c, -f, or -t options to apply specific updates.');
            return Command::SUCCESS;
        }

        $io->info(sprintf('Applying updates: %s', implode(', ', array_keys($customSets))));

        $configuration = new ModuleRefactorConfiguration(
            false,
            0,
            0,
            0,
            [],
            $customSets
        );

        $this->moduleUpdater->update($modulePath, $configuration);

        $io->success('Module successfully updated!');

        return Command::SUCCESS;
    }
}
