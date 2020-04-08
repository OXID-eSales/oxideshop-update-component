<?php declare(strict_types=1);
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\OxidEshopUpdateComponent\Module\Command;

use OxidEsales\OxidEshopUpdateComponent\Module\Service\ActiveModuleStateTransferringServiceInterface;
use OxidEsales\OxidEshopUpdateComponent\Module\Service\ModuleExtensionsSortingService;
use OxidEsales\OxidEshopUpdateComponent\Module\Service\ModuleSettingsTransferringServiceInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @internal
 */
final class TransferModuleDataToProjectConfigurationCommand extends Command
{
    /**
     * @var ActiveModuleStateTransferringServiceInterface
     */
    private $activeModuleStateTransferringService;

    /**
     * @var ModuleSettingsTransferringServiceInterface
     */
    private $moduleSettingsTransferringService;

    /**
     * @var ModuleExtensionsSortingService
     */
    private $moduleExtensionsSortingService;

    public function __construct(
        ActiveModuleStateTransferringServiceInterface $activeModuleStateTransferringService,
        ModuleSettingsTransferringServiceInterface $moduleSettingsTransferringService,
        ModuleExtensionsSortingService $moduleExtensionsSortingService
    ) {
        $this->activeModuleStateTransferringService = $activeModuleStateTransferringService;
        $this->moduleSettingsTransferringService = $moduleSettingsTransferringService;
        $this->moduleExtensionsSortingService = $moduleExtensionsSortingService;

        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $this->activeModuleStateTransferringService->transferAlreadyActiveModuleStateToProjectConfiguration();
        $output->writeln('');
        $this->moduleSettingsTransferringService->transferValuesFromDatabaseToProjectConfiguration();
        $output->writeln('');
        $this->moduleExtensionsSortingService->sort();
    }
}
