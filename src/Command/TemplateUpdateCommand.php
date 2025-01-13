<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\OxidEshopUpdateComponent\Command;

use Exception;
use OxidEsales\OxidEshopUpdateComponent\Template\Migration\TemplateFilterMigratorInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class TemplateUpdateCommand extends Command
{
    public const TEMPLATES_DIRECTORY = 'templates-directory';

    public function __construct(
        private readonly TemplateFilterMigratorInterface $templateFilterMigrator,
        private readonly LoggerInterface $logger
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument(
                self::TEMPLATES_DIRECTORY,
                InputArgument::REQUIRED,
                'The directory containing template files to update.'
            )
            ->setDescription('Updates template files in the specified directory.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $templateDirectory = $input->getArgument(self::TEMPLATES_DIRECTORY);

        try {
            $this->templateFilterMigrator->migrate($templateDirectory);

            $io->success('Template update process completed.');
        } catch (Exception $exception) {
            $io->error('Migration failed: Unexpected error. Check logs for details.');

            $this->logger->error(
                'Unexpected error during template update: ' . $exception->getMessage(),
                ['exception' => $exception]
            );

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
