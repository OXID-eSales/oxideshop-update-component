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

class RefactorModuleCommand extends Command
{
    public function __construct(
        private readonly ModuleUpdaterInterface $moduleUpdater
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Applies general code refactoring to a module')
            ->addArgument(
                'module-path',
                InputArgument::REQUIRED,
                'Path to the module directory'
            )
            ->addOption(
                'import-names',
                null,
                InputOption::VALUE_NONE,
                'Import names and remove unused imports'
            )
            ->addOption(
                'type-coverage-level',
                null,
                InputOption::VALUE_REQUIRED,
                'Set type coverage level (0-50)',
                0
            )
            ->addOption(
                'dead-code-level',
                null,
                InputOption::VALUE_REQUIRED,
                'Set dead code level (0-50)',
                0
            )
            ->addOption(
                'code-quality-level',
                null,
                InputOption::VALUE_REQUIRED,
                'Set code quality level (0-50)',
                0
            )
            ->addOption(
                'sets',
                null,
                InputOption::VALUE_IS_ARRAY | InputOption::VALUE_REQUIRED,
                'Enable prepared sets (deadCode, codeQuality, codingStyle, naming, typeDeclarations, ' .
                'earlyReturn, strictBooleans, privatization)'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $modulePath = $input->getArgument('module-path');

        $io->title(sprintf('Starting module refactoring for module at "%s"', $modulePath));

        $configuration = new ModuleRefactorConfiguration(
            $input->getOption('import-names'),
            (int)$input->getOption('type-coverage-level'),
            (int)$input->getOption('dead-code-level'),
            (int)$input->getOption('code-quality-level'),
            (array)$input->getOption('sets')
        );

        $this->moduleUpdater->update($modulePath, $configuration);

        $io->success('Module successfully refactored!');

        return self::SUCCESS;
    }
}
