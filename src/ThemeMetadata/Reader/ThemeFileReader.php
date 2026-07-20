<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\OxidEshopUpdateComponent\ThemeMetadata\Reader;

use OxidEsales\OxidEshopUpdateComponent\ThemeMetadata\Exception\ThemeFileNotFoundException;
use Symfony\Component\Filesystem\Path;

readonly class ThemeFileReader implements ThemeFileReaderInterface
{
    private const THEME_FILE_NAME = 'theme.php';

    public function read(string $themeDirectory): array
    {
        $themeFilePath = Path::join($themeDirectory, self::THEME_FILE_NAME);

        if (!is_file($themeFilePath)) {
            throw new ThemeFileNotFoundException("No theme.php found in '$themeDirectory'");
        }

        return (static function () use ($themeFilePath): array {
            $aTheme = [];
            include $themeFilePath;

            return $aTheme;
        })();
    }
}
