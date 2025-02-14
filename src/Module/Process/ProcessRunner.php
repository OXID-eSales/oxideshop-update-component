<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\OxidEshopUpdateComponent\Module\Process;

use OxidEsales\OxidEshopUpdateComponent\Module\Exception\ModuleUpdateException;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class ProcessRunner implements ProcessRunnerInterface
{
    public function run(array $command): void
    {
        $process = new Process($command, null);
        $process->setTimeout(3600);

        try {
            $process->mustRun(function ($type, $buffer) {
                echo $buffer;
            });
        } catch (ProcessFailedException $exception) {
            throw new ModuleUpdateException(
                sprintf(
                    "Process failed with exit code %d: %s\nError output: %s",
                    $process->getExitCode(),
                    $process->getErrorOutput(),
                    $exception->getMessage()
                )
            );
        }
    }
}
