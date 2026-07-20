<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\OxidEshopUpdateComponent\ThemeTemplate\Resolver;

use Symfony\Component\Console\Style\SymfonyStyle;

interface UndeclaredThemeSettingResolverInterface
{
    public function resolve(SymfonyStyle $io, bool $interactive, string $themeDirectory): void;
}
