<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\OxidEshopUpdateComponent\Command;

use OxidEsales\OxidEshopUpdateComponent\ProductImage\Migration\ProductImageMigratorInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Throwable;

class ProductImageMigrationCommand extends Command
{
    public function __construct(
        private readonly ProductImageMigratorInterface $productImageMigrator
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Migrates product images data from oxarticles to the new tables')
            ->addArgument(
                'batch-size',
                InputArgument::OPTIONAL,
                'Number of products to process per batch',
                5000
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $batchSize = (int) $input->getArgument('batch-size');

        $io->info("Starting product image migration with batch size: $batchSize");

        $progressBar = $io->createProgressBar();

        try {
            foreach ($this->productImageMigrator->migrate($batchSize) as $progress) {
                $progressBar->setMaxSteps($progress['total']);
                $progressBar->setProgress($progress['processed']);
            }

            $progressBar->finish();
            $io->newLine(2);
            $io->success('Product images successfully migrated!');
        } catch (Throwable $exception) {
            $progressBar->display();
            $io->newLine(2);
            $io->error('Unexpected Error: ' . $exception->getMessage());

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
