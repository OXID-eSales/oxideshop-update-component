<?php declare(strict_types=1);
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\OxidEshopUpdateComponent\Module\Command;

use OxidEsales\OxidEshopUpdateComponent\Module\Service\ModuleDataDeletionServiceInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command to delete certain module data from the database. This is needed to clean up the database before activating
 * the modules again. Otherwise, e.g. existing controllers would conflict on module activation.
 *
 * @internal
 */
final class DeleteModuleDataFromDatabaseCommand extends Command
{
    /**
     * @var ModuleDataDeletionServiceInterface
     */
    private $moduleDataDeletionService;

    /**
     * DeleteModuleDataFromDatabaseCommand constructor.
     * @param ModuleDataDeletionServiceInterface $moduleDataDeletionService
     */
    public function __construct(ModuleDataDeletionServiceInterface $moduleDataDeletionService)
    {
        $this->moduleDataDeletionService = $moduleDataDeletionService;
        parent::__construct();
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     */
    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $this->moduleDataDeletionService->deleteModuleDataFromDatabase();
    }
}
