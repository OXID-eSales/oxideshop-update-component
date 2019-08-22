<?php declare(strict_types=1);
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\OxidEshopUpdateComponent\Module\Service\ModuleExtensionsSorting;

/**
 * @internal
 */
class ExtensionsSorter
{
    public function sort(array $extensionsFromFile, array $extensionsFromDatabase): array
    {
        $extensionsFromFile = $this->sortShopClasses($extensionsFromFile, $extensionsFromDatabase);
        $sortedData = $this->sortModuleClasses($extensionsFromFile, $extensionsFromDatabase);

        return $sortedData;
    }

    private function sortShopClasses(array $extensionsFromFile, array $extensionsFromDatabase): array
    {
        $mergedExtensions = array_merge($extensionsFromDatabase, $extensionsFromFile);
        $mergedShopClassesNames = array_keys($mergedExtensions);

        uksort($extensionsFromFile, static function ($value1, $value2) use ($mergedShopClassesNames) {
            return (
                array_search($value1, $mergedShopClassesNames, true)
                > array_search($value2, $mergedShopClassesNames, true)
            );
        });
        return $extensionsFromFile;
    }

    private function sortModuleClasses(array $extensionsFromFile, array $classesFromDatabase): array
    {
        $sortedExtensions = [];
        foreach ($extensionsFromFile as $eshopClassName => $classesFromFile) {
            $sortedExtensions[$eshopClassName] = $classesFromFile;
            if (array_key_exists($eshopClassName, $classesFromDatabase)) {
                $mergedModuleClasses = $this->mergeModuleClasses(
                    $classesFromDatabase[$eshopClassName],
                    $classesFromFile
                );
                $sortedClasses = $classesFromFile;
                usort($sortedClasses, static function ($class1, $class2) use ($mergedModuleClasses) {
                    return (
                        array_search($class1, $mergedModuleClasses, true)
                        > array_search($class2, $mergedModuleClasses, true)
                    );
                });
                $sortedExtensions[$eshopClassName] = $sortedClasses;
            }
        }
        return $sortedExtensions;
    }

    private function mergeModuleClasses(array $moduleClassesFromDatabase, $moduleClassesFromFile): array
    {
        $flippedDatabaseExtensions = array_flip($moduleClassesFromDatabase);
        $flippedFileExtensions = array_flip($moduleClassesFromFile);
        $mergedClasses = array_merge($flippedDatabaseExtensions, $flippedFileExtensions);
        $mergedClasses = array_keys($mergedClasses);
        return $mergedClasses;
    }
}
