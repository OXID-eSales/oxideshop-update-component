<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\OxidEshopUpdateComponent\Command;

use OxidEsales\OxidEshopUpdateComponent\Config\Migration\ConfigurationMigratorInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class MigrateConfigCommand extends Command
{
    public function __construct(
        private readonly ConfigurationMigratorInterface $migrator
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Migrates config.inc.php to .env and parameters.yaml files');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $this->migrator->migrate();

        $io->success([
            'Configuration successfully migrated!',
            'Files updated:',
            '- .env',
            '- var/configuration/shops/parameters.yaml',
        ]);

        $io->note('Remember to remove the redundant config.inc.php file after verifying the migration.');

        return Command::SUCCESS;
    }
}
