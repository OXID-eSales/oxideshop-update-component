<?php declare(strict_types=1);
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\OxidEshopUpdateComponent\Tests\Unit\Service\ModuleExtensionsSorting;

use OxidEsales\OxidEshopUpdateComponent\Module\Service\ModuleExtensionsSorting\ExtensionsSorter;
use PHPUnit\Framework\TestCase;

final class ExtensionsSorterTest extends TestCase
{
    public function testSort()
    {
        $valuesToBeSorted = [
            'OxidEshopClass1' => [
                'Module3\Class',
                'Module2\Class',
                'Module1\Class',
            ],
            'OxidEshopClass3' => [
                'Module3\Class',
                'Module2\Class',
                'Module1\Class',
            ],
            'OxidEshopClass2' => [
                'Module2\Class',
                'Module3\Class',
            ]
        ];

        $valuesToSortBy = [
            'OxidEshopClass2' => [
                'Module3\Class',
                'NonExisting',
            ],
            'OxidEshopClass1' => [
                'Module1\Class',
                'Module2\Class'
            ],
        ];

        $output = [
            'OxidEshopClass2' => [
                'Module3\Class',
                'Module2\Class',
            ],
            'OxidEshopClass1' => [
                'Module1\Class',
                'Module2\Class',
                'Module3\Class',
            ],
            'OxidEshopClass3' => [
                'Module3\Class',
                'Module2\Class',
                'Module1\Class',
            ],
        ];

        $sorter = new ExtensionsSorter();
        $this->assertSame(
            $output,
            $sorter->sort($valuesToBeSorted, $valuesToSortBy)
        );
    }
}
