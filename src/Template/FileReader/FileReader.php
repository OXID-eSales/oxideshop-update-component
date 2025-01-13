<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\OxidEshopUpdateComponent\Template\FileReader;

use OxidEsales\OxidEshopUpdateComponent\Template\Exception\TemplateFileNotFoundException;
use Symfony\Component\Filesystem\Filesystem;

readonly class FileReader
{
    public function __construct(private Filesystem $filesystem)
    {
    }

    public function readFile(string $filePath): string
    {
        if (!$this->filesystem->exists($filePath)) {
            throw new TemplateFileNotFoundException(sprintf('File not found: %s', $filePath));
        }

        return file_get_contents($filePath);
    }
}
