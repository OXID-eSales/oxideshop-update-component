<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\OxidEshopUpdateComponent\Config\Writer;

use OxidEsales\EshopCommunity\Internal\Transition\Utility\BasicContextInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Yaml;

class ConfigurationWriter implements ConfigurationWriterInterface
{
    public function __construct(
        private readonly BasicContextInterface $context,
        private readonly Filesystem $filesystem
    ) {
    }

    public function writeEnvConfig(array $config): void
    {
        $envFile = $this->context->getShopRootPath() . '/.env';

        if ($this->filesystem->exists($envFile)) {
            $content = file_get_contents($envFile) . PHP_EOL;
            $content .= $this->formatConfig($config);
        } else {
            $content = $this->formatConfig($config);
        }

        $this->filesystem->dumpFile($envFile, $content);
    }

    public function writeParameterConfig(array $config): void
    {
        $parametersFile = $this->context->getProjectConfigurationDirectory() . '/parameters.yaml';
        $yamlContent = Yaml::dump(['parameters' => $config], 4);

        $this->filesystem->dumpFile($parametersFile, $yamlContent);
    }

    private function formatConfig(array $config): string
    {
        $lines = array_map(
            fn($key) => sprintf('%s=%s', $key, $this->formatValue($config[$key])),
            array_keys($config)
        );

        return rtrim(implode(PHP_EOL, $lines));
    }

    private function formatValue(mixed $value): string
    {
        if (!is_bool($value)) {
            return (string)$value;
        }

        return $value ? 'true' : 'false';
    }
}