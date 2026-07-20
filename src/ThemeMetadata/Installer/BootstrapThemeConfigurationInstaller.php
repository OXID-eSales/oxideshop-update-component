<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\OxidEshopUpdateComponent\ThemeMetadata\Installer;

use OxidEsales\EshopCommunity\Internal\Container\BootstrapContainerFactory;
use OxidEsales\EshopCommunity\Internal\Framework\Theme\Install\Service\ThemeConfigurationInstallerInterface;

readonly class BootstrapThemeConfigurationInstaller implements BootstrapThemeConfigurationInstallerInterface
{
    public function install(string $themeDirectory): void
    {
        BootstrapContainerFactory::getBootstrapContainer()
            ->get(ThemeConfigurationInstallerInterface::class)
            ->install($themeDirectory);
    }
}
