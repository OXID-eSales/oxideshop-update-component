<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\OxidEshopUpdateComponent\Command;

use OxidEsales\OxidEshopUpdateComponent\SslUrl\Migration\SslUrlConfigurationMigratorInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Throwable;

class MigrateSslUrlConfigurationCommand extends Command
{
    private string $forceOption = 'force';

    public function __construct(
        private readonly SslUrlConfigurationMigratorInterface $sslUrlConfigurationMigrator
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption($this->forceOption, null, InputOption::VALUE_NONE, 'Skip confirmation prompt.')
            ->setDescription(
                'Migrates sMallSSLShopURL and aLanguageSSLURLs to sMallShopURL and aLanguageURLs '
                . '(SSL values win) and removes the SSL variables from the oxconfig table.'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->warning(
            'This overwrites sMallShopURL and aLanguageURLs with the SSL values and permanently removes '
            . 'sMallSSLShopURL and aLanguageSSLURLs from the database. Make sure you have a database backup.'
        );

        if (!$input->getOption($this->forceOption) && !$io->confirm('Do you want to continue?', false)) {
            $io->note('Aborted. No data was migrated.');

            return Command::SUCCESS;
        }

        try {
            $this->sslUrlConfigurationMigrator->migrate();
        } catch (Throwable $exception) {
            $io->error('SSL URL configuration migration failed: ' . $exception->getMessage());

            return Command::FAILURE;
        }

        $io->success('SSL URL configuration successfully migrated.');

        return Command::SUCCESS;
    }
}
