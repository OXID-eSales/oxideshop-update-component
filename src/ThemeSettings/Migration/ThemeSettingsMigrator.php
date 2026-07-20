<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\OxidEshopUpdateComponent\ThemeSettings\Migration;

use OxidEsales\EshopCommunity\Internal\Framework\Theme\Configuration\Dao\ThemeConfigurationDaoInterface;
use OxidEsales\EshopCommunity\Internal\Framework\Theme\Configuration\DataObject\ThemeConfiguration;
use OxidEsales\EshopCommunity\Internal\Framework\Theme\Setting\Setting;
use OxidEsales\EshopCommunity\Internal\Transition\Utility\ContextInterface;
use OxidEsales\OxidEshopUpdateComponent\ThemeSettings\Fetcher\ThemeConfigurationFetcherInterface;

readonly class ThemeSettingsMigrator implements ThemeSettingsMigratorInterface
{
    public function __construct(
        private ContextInterface $context,
        private ThemeConfigurationFetcherInterface $themeConfigurationFetcher,
        private ThemeConfigurationDaoInterface $themeConfigurationDao,
    ) {
    }

    public function migrate(): ThemeSettingsMigrationResult
    {
        $themesWithoutConfiguration = [];

        foreach ($this->context->getAllShopIds() as $shopId) {
            foreach ($this->migrateSettingsForShop($shopId) as $themeId) {
                $themesWithoutConfiguration[$themeId] = true;
            }

            $this->migrateActiveTheme($shopId);
        }

        return new ThemeSettingsMigrationResult(array_keys($themesWithoutConfiguration));
    }

    private function migrateSettingsForShop(int $shopId): array
    {
        $themesWithoutConfiguration = [];

        foreach ($this->themeConfigurationFetcher->fetchThemeSettings($shopId) as $themeId => $settings) {
            if (!$this->themeConfigurationDao->exists($themeId, $shopId)) {
                $themesWithoutConfiguration[] = $themeId;

                continue;
            }

            $configuration = $this->themeConfigurationDao->get($themeId, $shopId);

            foreach ($settings as $setting) {
                $this->applySetting($configuration, $setting);
            }

            $this->themeConfigurationDao->save($configuration, $shopId);
        }

        return $themesWithoutConfiguration;
    }

    private function migrateActiveTheme(int $shopId): void
    {
        $activeThemeId = $this->themeConfigurationFetcher->fetchActiveThemeId($shopId);

        if ($activeThemeId === '') {
            return;
        }

        foreach ($this->themeConfigurationDao->getAll($shopId) as $configuration) {
            $configuration->setActivated($configuration->getId() === $activeThemeId);
            $this->themeConfigurationDao->save($configuration, $shopId);
        }
    }

    private function applySetting(ThemeConfiguration $configuration, array $setting): void
    {
        $existingSetting = $configuration->getSettingByName($setting['name']);

        if ($existingSetting !== null) {
            $existingSetting->setValue($setting['value']);

            return;
        }

        $configuration->addThemeSetting(
            (new Setting())
                ->setName($setting['name'])
                ->setType($setting['type'])
                ->setValue($setting['value'])
        );
    }
}
