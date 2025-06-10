<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\OxidEshopUpdateComponent\Command;

use OxidEsales\OxidEshopUpdateComponent\DatabaseSchema\DatabaseSchemaModifierInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Throwable;

class DatabaseSchemaUpdateCommand extends Command
{
    public function __construct(
        private readonly DatabaseSchemaModifierInterface $databaseSchemaModifier
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Executes database schema modifications, including removing outdated columns');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $this->databaseSchemaModifier->updateDatabaseSchema();

            $io->success('Database schema successfully updated!');
        } catch (Throwable $exception) {
            $io->error('Unexpected Error: ' . $exception->getMessage());

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
