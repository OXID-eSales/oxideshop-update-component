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
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class DropLegacyImageColumnsCommand extends Command
{
    public function __construct(
        private readonly DatabaseSchemaModifierInterface $databaseSchemaModifier
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription(<<<'EOF'
Drops legacy image columns (OXPIC1-12, OXTHUMB, OXICON) from oxarticles table.
<error>ATTENTION: This is an irreversible operation. Run oe:update:migrate-product-images first!</error>
EOF)
            ->addOption(
                'force',
                'f',
                InputOption::VALUE_NONE,
                'Skip confirmation prompt (use with caution!)'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $force = $input->getOption('force');

        $io->warning([
            'WARNING: This command will DROP the following columns from oxarticles:',
            'OXPIC1, OXPIC2, OXPIC3, OXPIC4, OXPIC5, OXPIC6, OXPIC7, OXPIC8,',
            'OXPIC9, OXPIC10, OXPIC11, OXPIC12, OXTHUMB, OXICON',
            '',
            'This operation is IRREVERSIBLE!',
        ]);

        $io->caution([
            'Before proceeding, ensure you have run:',
            './vendor/bin/oe-console oe:update:migrate-product-images',
            '',
            'If you have not migrated the product images yet, this data will be PERMANENTLY LOST!',
        ]);

        if (!$force && !$io->confirm('Do you want to proceed?', false)) {
            $io->note('Operation cancelled.');
            return Command::SUCCESS;
        }

        $this->databaseSchemaModifier->updateDatabaseSchema();

        $io->success('Database schema successfully updated!');

        return Command::SUCCESS;
    }
}
