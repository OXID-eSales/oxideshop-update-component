<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\OxidEshopUpdateComponent\ThemeSettings\Fetcher;

use Doctrine\DBAL\ArrayParameterType;
use OxidEsales\EshopCommunity\Internal\Framework\Database\QueryBuilderFactoryInterface;
use OxidEsales\OxidEshopUpdateComponent\ThemeSettings\Transformer\ConfigValueDecoderInterface;

readonly class ThemeConfigurationFetcher implements ThemeConfigurationFetcherInterface
{
    private const MODULE_THEME_PREFIX = 'theme:';

    public function __construct(
        private QueryBuilderFactoryInterface $queryBuilderFactory,
        private ConfigValueDecoderInterface $configValueDecoder,
    ) {
    }

    public function fetchThemeSettings(int $shopId): array
    {
        $rows = $this->queryBuilderFactory->create()
            ->select('oxmodule', 'oxvarname', 'oxvartype', 'oxvarvalue')
            ->from('oxconfig')
            ->where('oxshopid = :shopId')
            ->andWhere('oxmodule LIKE :themeModulePattern')
            ->setParameter('shopId', $shopId)
            ->setParameter('themeModulePattern', self::MODULE_THEME_PREFIX . '%')
            ->executeQuery()
            ->fetchAllAssociative();

        $settingsByTheme = [];

        foreach ($rows as $row) {
            $themeId = substr($row['oxmodule'], strlen(self::MODULE_THEME_PREFIX));
            $settingsByTheme[$themeId][] = [
                'name' => $row['oxvarname'],
                'type' => $row['oxvartype'],
                'value' => $this->configValueDecoder->decode($row['oxvartype'], (string) $row['oxvarvalue']),
            ];
        }

        return $settingsByTheme;
    }

    public function fetchActiveThemeId(int $shopId): string
    {
        $values = $this->queryBuilderFactory->create()
            ->select('oxvarname', 'oxvarvalue')
            ->from('oxconfig')
            ->where('oxshopid = :shopId')
            ->andWhere('oxmodule = :shopModule')
            ->andWhere('oxvarname IN (:names)')
            ->setParameter('shopId', $shopId)
            ->setParameter('shopModule', '')
            ->setParameter('names', ['sTheme', 'sCustomTheme'], ArrayParameterType::STRING)
            ->executeQuery()
            ->fetchAllKeyValue();

        $customTheme = (string) ($values['sCustomTheme'] ?? '');
        $theme = (string) ($values['sTheme'] ?? '');

        return $customTheme !== '' ? $customTheme : $theme;
    }
}
