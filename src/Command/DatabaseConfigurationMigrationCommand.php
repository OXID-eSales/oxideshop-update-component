<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\OxidEshopUpdateComponent\Command;

use OxidEsales\OxidEshopUpdateComponent\DatabaseConfig\Migrator\DatabaseMigrationFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class DatabaseConfigurationMigrationCommand extends Command
{
    private string $removeOldParametersOption = 'remove-old-configuration';

    public function __construct(
        private readonly DatabaseMigrationFactory $migrationFactory
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Migrates configuration values from the database to parameters.yaml files');
        $this->addArgument(
            $this->removeOldParametersOption,
            InputArgument::OPTIONAL,
            'Remove old configuration parameters.',
            false
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $shouldRemoveOldParameters = (bool) $input->getArgument($this->removeOldParametersOption);

        try {
            $migrationService = $this->migrationFactory->createMigrationService($shouldRemoveOldParameters);
            $migrationService->migrateDatabaseToContainerConfiguration();

            $io->success('Database configuration successfully migrated!');
        } catch (\Throwable $exception) {
            $io->error('Unexpected Error: ' . $exception->getMessage());

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
