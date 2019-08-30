<?php declare(strict_types=1);
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\OxidEshopUpdateComponent\Module\Command;

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
     */
    public function __construct()
    {
        echo "ghy";

        parent::__construct();
    }

    /**
     * @inheritdoc
     */
    protected function configure(): void
    {
        $this
            ->setName(
                'oe:oxideshop-update-component:delete_module_data_from_database'
            )
            ->setDescription(
                'Delete module data from the database.'
            );
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     */
    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        // delete controllers
        // delete aDisabledModules
        // delete all other module related information where module =
    }
}
