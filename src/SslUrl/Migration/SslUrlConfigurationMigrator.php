<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\OxidEshopUpdateComponent\SslUrl\Migration;

use Doctrine\DBAL\ArrayParameterType;
use OxidEsales\EshopCommunity\Internal\Framework\Database\QueryBuilderFactoryInterface;
use OxidEsales\EshopCommunity\Internal\Transition\Utility\ContextInterface;

readonly class SslUrlConfigurationMigrator implements SslUrlConfigurationMigratorInterface
{
    private const MALL_URL = 'sMallShopURL';
    private const MALL_SSL_URL = 'sMallSSLShopURL';
    private const LANGUAGE_URLS = 'aLanguageURLs';
    private const LANGUAGE_SSL_URLS = 'aLanguageSSLURLs';

    public function __construct(
        private ContextInterface $context,
        private QueryBuilderFactoryInterface $queryBuilderFactory,
    ) {}

    public function migrate(): void
    {
        foreach ($this->context->getAllShopIds() as $shopId) {
            $this->migrateMallUrl($shopId);
            $this->migrateLanguageUrls($shopId);
        }

        $this->removeSslVariables();
    }

    private function migrateMallUrl(int $shopId): void
    {
        $sslShopUrl = (string) $this->fetchValue($shopId, self::MALL_SSL_URL);
        if ($sslShopUrl === '') {
            return;
        }

        $this->saveValue($shopId, self::MALL_URL, 'str', $sslShopUrl);
    }

    private function migrateLanguageUrls(int $shopId): void
    {
        $languageSslUrls = unserialize(
            (string) $this->fetchValue($shopId, self::LANGUAGE_SSL_URLS),
            ['allowed_classes' => false]
        );

        if (!is_array($languageSslUrls)) {
            return;
        }

        $languageSslUrls = array_filter(
            $languageSslUrls,
            static fn ($url): bool => is_string($url) && $url !== ''
        );

        if ($languageSslUrls === []) {
            return;
        }

        $languageUrls = unserialize(
            (string) $this->fetchValue($shopId, self::LANGUAGE_URLS),
            ['allowed_classes' => false]
        );

        if (!is_array($languageUrls)) {
            $languageUrls = [];
        }

        foreach ($languageSslUrls as $languageId => $languageSslUrl) {
            $languageUrls[$languageId] = $languageSslUrl;
        }

        $this->saveValue($shopId, self::LANGUAGE_URLS, 'arr', serialize($languageUrls));
    }

    private function saveValue(int $shopId, string $name, string $type, string $value): void
    {
        if ($this->fetchValue($shopId, $name) !== false) {
            $this->queryBuilderFactory->create()
                ->update('oxconfig')
                ->set('oxvarvalue', ':value')
                ->where('oxshopid = :shopId')
                ->andWhere('oxmodule = :module')
                ->andWhere('oxvarname = :name')
                ->setParameters([
                    'value' => $value,
                    'shopId' => $shopId,
                    'module' => '',
                    'name' => $name,
                ])
                ->executeStatement();

            return;
        }

        $this->queryBuilderFactory->create()
            ->insert('oxconfig')
            ->values([
                'oxid' => ':id',
                'oxshopid' => ':shopId',
                'oxmodule' => ':module',
                'oxvarname' => ':name',
                'oxvartype' => ':type',
                'oxvarvalue' => ':value',
            ])
            ->setParameters([
                'id' => bin2hex(random_bytes(16)),
                'shopId' => $shopId,
                'module' => '',
                'name' => $name,
                'type' => $type,
                'value' => $value,
            ])
            ->executeStatement();
    }

    private function fetchValue(int $shopId, string $name): string|false
    {
        return $this->queryBuilderFactory->create()
            ->select('oxvarvalue')
            ->from('oxconfig')
            ->where('oxshopid = :shopId')
            ->andWhere('oxmodule = :module')
            ->andWhere('oxvarname = :name')
            ->setParameters([
                'shopId' => $shopId,
                'module' => '',
                'name' => $name,
            ])
            ->executeQuery()
            ->fetchOne();
    }

    private function removeSslVariables(): void
    {
        $this->queryBuilderFactory->create()
            ->delete('oxconfig')
            ->where('oxmodule = :module')
            ->andWhere('oxvarname IN (:names)')
            ->setParameter('module', '')
            ->setParameter(
                'names',
                [self::MALL_SSL_URL, self::LANGUAGE_SSL_URLS],
                ArrayParameterType::STRING
            )
            ->executeStatement();
    }
}
