<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\OxidEshopUpdateComponent\Template\Migration;

use OxidEsales\OxidEshopUpdateComponent\Template\Transformer\TwigFilterTransformerInterface;
use Symfony\Component\Finder\Finder;

readonly class TemplateFilterMigrator implements TemplateFilterMigratorInterface
{
    public function __construct(
        private TwigFilterTransformerInterface $transformer
    ) {
    }

    public function migrate(string $templateDirectory): void
    {
        $finder = new Finder();
        $finder->files()->in($templateDirectory)->name('*.twig');

        foreach ($finder as $file) {
            $this->transformer->replace($file->getRealPath());
        }
    }
}
