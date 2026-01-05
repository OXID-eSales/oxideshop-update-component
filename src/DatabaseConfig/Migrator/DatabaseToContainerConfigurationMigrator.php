<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\OxidEshopUpdateComponent\DatabaseConfig\Migrator;

use OxidEsales\EshopCommunity\Internal\Framework\DIContainer\ContainerBuilder;
use OxidEsales\EshopCommunity\Internal\Framework\DIContainer\Dao\ParameterDaoInterface;
use OxidEsales\EshopCommunity\Internal\Transition\Utility\BasicContext;
use OxidEsales\EshopCommunity\Internal\Transition\Utility\ContextInterface;
use OxidEsales\OxidEshopUpdateComponent\DatabaseConfig\Fetcher\DatabaseConfigurationFetcherInterface;
use OxidEsales\OxidEshopUpdateComponent\DatabaseConfig\ParametersProvider\ConfigurationParametersProviderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder as SymfonyContainerBuilder;

class DatabaseToContainerConfigurationMigrator implements DatabaseToContainerConfigurationMigratorInterface
{
    public function __construct(
        private readonly ContextInterface $context,
        private readonly ConfigurationParametersProviderInterface $configurationParametersProvider,
        private readonly DatabaseConfigurationFetcherInterface $configurationFetcher,
        private readonly ParameterDaoInterface $containerParameterDao
    ) {
    }

    public function migrateDatabaseToContainerConfiguration(): void
    {
        $shops = $this->context->getAllShopIds();
        foreach ($shops as $shopId) {
            $this->migrateConfigurationForShop($shopId);
        }
    }

    protected function migrateConfigurationForShop(int $shopId): void
    {
        $databaseConfigurationParameters = $this->configurationFetcher->fetchDatabaseConfigurationValues(
            $shopId,
            $this->configurationParametersProvider->getDatabaseConfigurationParameters()
        );

        $container = (new ContainerBuilder($this->context, $shopId))->getContainer();
        $container->compile();
        $containerParameters = $this->filterDuplicateContainerParameters(
            $this->mapDatabaseToContainerParameters($databaseConfigurationParameters),
            $container
        );

        $this->writeContainerParameters($containerParameters, $shopId);
    }

    private function mapDatabaseToContainerParameters(array $databaseConfigurationParameters): array
    {
        $mappedParameters = [];
        foreach ($databaseConfigurationParameters as $parameterName => $parameterValue) {
            $mappedParameters[
                $this->configurationParametersProvider->getContainerParameterName($parameterName)
            ] = $parameterValue;
        }

        return $mappedParameters;
    }

    private function filterDuplicateContainerParameters(
        array $containerParameters,
        SymfonyContainerBuilder $container
    ): array {
        return array_filter(
            $containerParameters,
            fn($value, $parameterName) =>
                !$container->hasParameter($parameterName) ||
                $container->getParameter($parameterName) !== $value,
            ARRAY_FILTER_USE_BOTH
        );
    }

    private function writeContainerParameters(array $containerParameters, int $shopId): void
    {
        foreach ($containerParameters as $parameterName => $parameterValue) {
            $this->containerParameterDao->add($parameterName, $parameterValue, $shopId);
        }
    }
}
