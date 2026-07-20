<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\OxidEshopUpdateComponent\ThemeMetadata\Installer;

interface BootstrapThemeConfigurationInstallerInterface
{
    public function install(string $themeDirectory): void;
}
