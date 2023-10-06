<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\OxidEshopUpdateComponent\Rector\MethodCallRename;

use Rector\Renaming\ValueObject\MethodCallRename;

final class MethodCallRenameGenerator
{
    private string $temporaryFile;

    /**
     * Forms array with MethodCallRename for underscored methods if class name and old method from the input file
     * were found in module
     * @throws \ReflectionException
     */
    public function getMethodCallRenamesForModule(array $classMethodArray, string $modulePath): array
    {
        $methodCallRenames = [];
        $this->temporaryFile = tempnam(sys_get_temp_dir(), 'module-contents-dump.tmp');
        $this->dumpModuleContentsToTemporaryFile($modulePath);
        foreach ($classMethodArray as [$class, $oldMethod, $newMethod]) {
            $className = (new \ReflectionClass($class))->getShortName();
            if (!$this->temporaryFileContainsStrings($className, $oldMethod)) {
                continue;
            }
            $methodCallRenames[] = new MethodCallRename($class, $oldMethod, $newMethod);
        }
        unlink($this->temporaryFile);

        return $methodCallRenames;
    }

    private function dumpModuleContentsToTemporaryFile(string $modulePath): void
    {
        $moduleFilesIterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($modulePath)
        );
        $modulePhpFilesIterator = new \RegexIterator(
            $moduleFilesIterator,
            '/\.php$/'
        );
        $output = fopen($this->temporaryFile, 'wb+');
        foreach ($modulePhpFilesIterator as $file) {
            fwrite($output, file_get_contents($file->getPathname()));
        }
    }

    private function temporaryFileContainsStrings(string $className, string $methodName): bool
    {
        $content = file_get_contents($this->temporaryFile);

        return str_contains($content, $methodName) && str_contains($content, $className);
    }
}
