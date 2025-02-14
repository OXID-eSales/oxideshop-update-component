<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\OxidEshopUpdateComponent\Module\Update;

use OxidEsales\EshopCommunity\Internal\Framework\Module\Path\ModulePathResolverInterface;
use OxidEsales\EshopCommunity\Internal\Transition\Utility\BasicContextInterface;
use OxidEsales\OxidEshopUpdateComponent\Module\Configuration\ModuleRefactorConfiguration;
use OxidEsales\OxidEshopUpdateComponent\Module\Factory\RectorConfigFactoryInterface;
use OxidEsales\OxidEshopUpdateComponent\Module\Process\ProcessRunnerInterface;
use OxidEsales\OxidEshopUpdateComponent\Module\Validator\ModuleValidatorInterface;

class ModuleUpdater implements ModuleUpdaterInterface
{
    public function __construct(
        private readonly ModuleValidatorInterface $validator,
        private readonly ProcessRunnerInterface $processRunner,
        private readonly RectorConfigFactoryInterface $rectorConfigFactory
    ) {
    }

    public function update(string $modulePath, ModuleRefactorConfiguration $configuration): void
    {
        $this->validator->validate($modulePath);

        $rectorConfigFile = $this->rectorConfigFactory->createConfigurationFile($configuration, $modulePath);

        $this->processRunner->run([
            'vendor/bin/rector',
            'process',
            $modulePath,
            '--config',
            $rectorConfigFile,
            '--no-diffs'
        ]);
    }
}
