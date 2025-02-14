<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\OxidEshopUpdateComponent\Module\Factory;

use OxidEsales\OxidEshopUpdateComponent\Module\Configuration\ModuleRefactorConfiguration;

class RectorConfigFactory implements RectorConfigFactoryInterface
{
    public function createConfigurationFile(ModuleRefactorConfiguration $configuration, string $modulePath): string
    {
        $template = <<<'PHP'
<?php

use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withSkip(%skipPath%)
    ->withParallel()
    ->withTypeCoverageLevel(%typeCoverageLevel%)
    ->withDeadCodeLevel(%deadCodeLevel%)
    ->withCodeQualityLevel(%codeQualityLevel%)
    %preparedSetsCode%
    %importNamesCode%
    %customSetsCode%
;
PHP;
        $skipPath = $this->buildSkipPath($modulePath);
        $preparedSetsCode = $this->buildPreparedSetsCode($configuration);
        $importNamesCode = $this->buildImportNamesCode($configuration);
        $customSetsCode = $this->buildCustomSetsCode($configuration);
        $replacements = [
            '%skipPath%' => $skipPath,
            '%typeCoverageLevel%' => $configuration->typeCoverageLevel,
            '%deadCodeLevel%' => $configuration->deadCodeLevel,
            '%codeQualityLevel%' => $configuration->codeQualityLevel,
            '%preparedSetsCode%' => $preparedSetsCode,
            '%importNamesCode%' => $importNamesCode,
            '%customSetsCode%' => $customSetsCode,
        ];
        $configContent = strtr($template, $replacements);
        $tempConfigFile = tempnam(sys_get_temp_dir(), 'rector_config_') . '.php';
        file_put_contents($tempConfigFile, $configContent);
        return $tempConfigFile;
    }

    private function buildSkipPath(string $modulePath): string
    {
        return var_export([$modulePath . '/vendor/*'], true);
    }

    private function buildPreparedSetsCode(ModuleRefactorConfiguration $configuration): string
    {
        $defaultSets = [
            'deadCode',
            'codeQuality',
            'codingStyle',
            'typeDeclarations',
            'privatization',
            'naming',
            'earlyReturn',
            'strictBooleans',
        ];
        $setParams = array_map(
            fn(string $set): string => sprintf('%s: %s', $set, in_array($set, $configuration->preparedSets, true) ? 'true' : 'false'),
            $defaultSets
        );
        return "\n    ->withPreparedSets(" . implode(', ', $setParams) . ")";
    }

    private function buildImportNamesCode(ModuleRefactorConfiguration $configuration): string
    {
        return $configuration->importNames ? "\n    ->withImportNames(removeUnusedImports: true)" : "";
    }

    private function buildCustomSetsCode(ModuleRefactorConfiguration $configuration): string
    {
        $enabledCustomSets = array_keys(array_filter($configuration->customSets));
        if (empty($enabledCustomSets)) {
            return "";
        }
        $customSetPaths = array_map(
            fn(string $set): string => dirname(__DIR__) . "/Rector/Sets/{$set}.php",
            $enabledCustomSets
        );
        return "\n    ->withSets(" . var_export($customSetPaths, true) . ")";
    }
}
