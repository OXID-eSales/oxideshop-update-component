<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\OxidEshopUpdateComponent\ThemeTemplate\Builder;

use Symfony\Component\Console\Style\SymfonyStyle;

interface InteractiveThemeSettingBuilderInterface
{
    public function build(SymfonyStyle $io, string $name, string $themeDirectory): ThemeSettingDefinition;
}
