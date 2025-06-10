<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\OxidEshopUpdateComponent\DatabaseSchema;

readonly class DatabaseSchemaModifier implements DatabaseSchemaModifierInterface
{
    public function __construct(private iterable $updaters)
    {
    }

    public function updateDatabaseSchema(): void
    {
        foreach ($this->updaters as $updater) {
            $updater->update();
        }
    }
}
