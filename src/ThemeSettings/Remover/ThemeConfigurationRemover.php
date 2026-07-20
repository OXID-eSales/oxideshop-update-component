<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\OxidEshopUpdateComponent\ThemeSettings\Remover;

use Doctrine\DBAL\ArrayParameterType;
use OxidEsales\EshopCommunity\Internal\Framework\Database\QueryBuilderFactoryInterface;

readonly class ThemeConfigurationRemover implements ThemeConfigurationRemoverInterface
{
    private const MODULE_THEME_PREFIX = 'theme:';
    private const ACTIVE_THEME_VARIABLES = ['sTheme', 'sCustomTheme'];

    public function __construct(private QueryBuilderFactoryInterface $queryBuilderFactory)
    {
    }

    public function remove(): void
    {
        $this->queryBuilderFactory->create()
            ->delete('oxconfig')
            ->where('oxmodule LIKE :themeModulePattern')
            ->orWhere('oxmodule = :shopModule AND oxvarname IN (:activeThemeVariables)')
            ->setParameter('themeModulePattern', self::MODULE_THEME_PREFIX . '%')
            ->setParameter('shopModule', '')
            ->setParameter('activeThemeVariables', self::ACTIVE_THEME_VARIABLES, ArrayParameterType::STRING)
            ->executeStatement();
    }
}
