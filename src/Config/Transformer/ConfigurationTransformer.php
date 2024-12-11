<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\OxidEshopUpdateComponent\Config\Transformer;

use OxidEsales\EshopCommunity\Core\Di\ContainerFacade;

class ConfigurationTransformer implements ConfigurationTransformerInterface
{
    private readonly array $environmentVariableMapping;
    private readonly array $parameterMapping;

    public function __construct()
    {
        $this->environmentVariableMapping = [
            'sLogLevel' => 'OXID_LOG_LEVEL',
            'iDebug' => 'OXID_DEBUG_MODE',
            'sCompileDir' => 'OXID_BUILD_DIRECTORY'
        ];

        $this->parameterMapping = [
            'sAdminSSLURL' => 'oxid_esales.shop_admin_url',
            'sSSLAltImageUrl' => 'oxid_esales.alternative_image_url',
            'sShopLogo' => 'oxid_esales.shop_logo',
            'aAllowedUploadTypes' => 'oxid_esales.allowed_uploaded_types',
            'blSeoLogging' => 'oxid_esales.log_not_seo_urls',
            'blForceSessionStart' => 'oxid_esales.force_session_start',
            'blSessionUseCookies' => 'oxid_esales.cookies_session',
            'aCookieDomains' => 'oxid_esales.cookie_domains',
            'aCookiePaths' => 'oxid_esales.cookie_paths',
            'disallowForceSessionIdInRequest' => 'oxid_esales.disallow_force_session_id',
            'aRobots' => 'oxid_esales.search_engine_list',
            'aTrustedIPs' => 'oxid_esales.trusted_ips',
            'iBasketReservationCleanPerRequest' => 'oxid_esales.basket_reservation_cleanup_rate',
            'aUserComponentNames' => 'oxid_esales.cacheable_user_components',
            'aMultiLangTables' => 'oxid_esales.multilingual_tables',
            'blUseCron' => 'oxid_esales.cron_enabled',
            'blSkipViewUsage' => 'oxid_esales.skip_database_views_usage',
            'blUseRightsRoles' => 'oxid_esales.user_rights_roles_mode',
            'aMultishopArticleFields' => 'oxid_esales.multi_shop_article_fields',
            'blShowUpdateViews' => 'oxid_esales.show_update_views_button',
            'blLogChangesInAdmin' => 'oxid_esales.log_admin_queries',
            'blMallSharedBasket' => 'oxid_esales.mall_shared_basket',
            'blSeoMode' => 'oxid_esales.seo_mode',
            'iCreditRating' => 'oxid_esales.shop_credit_rating',
            'blDemoShop' => 'oxid_esales.demo_shop_mode',
            'iPicCount' => 'oxid_esales.max_product_pictures_count',
            'aMultiShopTables' => 'oxid_esales.multi_shop_tables',
            'aRequireSessionWithParams' => 'oxid_esales.session_init_params'
        ];
    }

    public function transformToEnvConfig(array $config): array
    {
        $envValues = [
            'OXID_ENV' => 'prod',
            'OXID_DEFAULT_TIMEZONE' => date_default_timezone_get(),
            'OXID_DB_URL' => $this->createDatabaseUrl($config),
            'OXID_SHOP_BASE_URL' => $this->getShopUrl($config),
        ];

        return array_merge($envValues, $this->mapValues($config, $this->environmentVariableMapping));
    }

    public function transformToParameterConfig(array $config): array
    {
        $mappedValues = $this->mapValues($config, $this->parameterMapping);

        return $this->filterExistingDefaultValues($mappedValues);
    }

    private function mapValues(array $config, array $mapping): array
    {
        $values = [];
        foreach ($mapping as $source => $target) {
            if (isset($config[$source]) && (!is_array($config[$source]) || !empty($config[$source]))) {
                $values[$target] = $this->transformValue($source, $config[$source]);
            }
        }

        return $values;
    }

    private function transformValue(string $key, mixed $value): mixed
    {
        return match ($key) {
            'iDebug' => $value !== 0,
            default => $value
        };
    }

    private function filterExistingDefaultValues(array $values): array
    {
        return array_filter(
            $values,
            fn($value, $parameterName) =>
                !ContainerFacade::hasParameter($parameterName) ||
                ContainerFacade::getParameter($parameterName) !== $value,
            ARRAY_FILTER_USE_BOTH
        );
    }

    private function getShopUrl(array $config): string
    {
        return $config['sSSLShopURL'] ?? $config['sShopURL'];
    }

    private function createDatabaseUrl(array $config): string
    {
        return sprintf(
            'mysql://%s:%s@%s:%s/%s?charset=utf8&driverOptions[1002]="SET @@SESSION.sql_mode=\"\""',
            $config['dbUser'],
            $config['dbPwd'],
            $config['dbHost'],
            $config['dbPort'],
            $config['dbName']
        );
    }
}