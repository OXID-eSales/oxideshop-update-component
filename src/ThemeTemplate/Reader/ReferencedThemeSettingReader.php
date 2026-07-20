<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\OxidEshopUpdateComponent\ThemeTemplate\Reader;

use OxidEsales\OxidEshopUpdateComponent\ThemeTemplate\Reference\ThemeSettingReferenceInterface;
use Symfony\Component\Finder\Finder;

readonly class ReferencedThemeSettingReader implements ReferencedThemeSettingReaderInterface
{
    private const TEMPLATE_EXTENSION = 'twig';

    public function __construct(private ThemeSettingReferenceInterface $themeSettingReference)
    {
    }

    public function read(string $themeDirectory): array
    {
        $referencedSettings = [];

        $finder = (new Finder())->files()->in($themeDirectory)->name('*.' . self::TEMPLATE_EXTENSION);

        foreach ($finder as $file) {
            foreach ($this->themeSettingReference->names((string) file_get_contents($file->getPathname())) as $name) {
                $referencedSettings[$name] = true;
            }
        }

        return array_keys($referencedSettings);
    }
}
