<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\OxidEshopUpdateComponent\ThemeTemplate\Builder;

use OxidEsales\OxidEshopUpdateComponent\ThemeTemplate\Configuration\ThemeSettingsConfigFileInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

readonly class InteractiveThemeSettingBuilder implements InteractiveThemeSettingBuilderInterface
{
    private const SETTING_TYPES = ['str', 'bool', 'num', 'arr', 'aarr', 'select'];

    public function __construct(private ThemeSettingsConfigFileInterface $themeSettingsConfigFile)
    {
    }

    public function build(SymfonyStyle $io, string $name, string $themeDirectory): ThemeSettingDefinition
    {
        $type = $io->choice(sprintf("Type for '%s'", $name), $this->oneBasedChoices(self::SETTING_TYPES), 'str');
        $constraints = $type === 'select' ? $this->askConstraints($io, $name) : [];
        $value = $this->askValue($io, $name, $type, $constraints);
        $group = $this->askGroup($io, $name, $themeDirectory);

        return new ThemeSettingDefinition($name, $type, $value, $group, $constraints);
    }

    private function askConstraints(SymfonyStyle $io, string $name): array
    {
        return array_values(array_filter(array_map(
            'trim',
            explode(',', (string) $io->ask(sprintf("Allowed values for '%s' (comma-separated)", $name), ''))
        )));
    }

    private function askValue(SymfonyStyle $io, string $name, string $type, array $constraints): mixed
    {
        $question = sprintf("Value for '%s'", $name);

        return match ($type) {
            'bool' => $io->choice($question, ['true', 'false'], 'false') === 'true',
            'num' => $this->toNumber((string) $io->ask($question, '0')),
            'arr' => array_values(array_filter(array_map(
                'trim',
                explode(',', (string) $io->ask($question . ' (comma-separated)', ''))
            ))),
            'aarr' => $this->parseAssociative((string) $io->ask($question . ' (comma-separated key:value pairs)', '')),
            'select' => $constraints !== []
                ? (string) $io->choice($question, $this->oneBasedChoices($constraints), $constraints[0])
                : (string) $io->ask($question, ''),
            default => (string) $io->ask($question, ''),
        };
    }

    private function parseAssociative(string $input): array
    {
        $pairs = array_filter(array_map('trim', explode(',', $input)));
        $result = [];

        foreach ($pairs as $pair) {
            [$key, $value] = array_pad(explode(':', $pair, 2), 2, '');
            $key = trim($key);

            if ($key !== '') {
                $result[$key] = trim($value);
            }
        }

        return $result;
    }

    private function oneBasedChoices(array $options): array
    {
        return array_combine(range(1, count($options)), array_values($options));
    }

    private function askGroup(SymfonyStyle $io, string $name, string $themeDirectory): string
    {
        $existingGroups = $this->themeSettingsConfigFile->getGroupNames($themeDirectory);
        $question = sprintf("Group for '%s'", $name);

        if ($existingGroups !== []) {
            $question .= ' (existing: ' . implode(', ', $existingGroups) . ')';
        }

        return (string) $io->ask($question, $existingGroups[0] ?? 'general');
    }

    private function toNumber(string $value): int|float
    {
        return str_contains($value, '.') ? (float) $value : (int) $value;
    }
}
