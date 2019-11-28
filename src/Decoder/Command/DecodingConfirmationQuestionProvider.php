<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\OxidEshopUpdateComponent\Decoder\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

trait DecodingConfirmationQuestionProvider
{
    protected function userConfirmation(InputInterface $input, OutputInterface $output): bool
    {
        $question = new ConfirmationQuestion(
            '<question>All of the config table values will be decoded without possibility to revert.'
            . PHP_EOL . 'We recommend before running this command to make database backup'
            . PHP_EOL . 'Do you still want to proceed?</question> (y/N)',
            false
        );
        $helper = $this->getHelper('question');

        if (!$helper->ask($input, $output, $question)) {
            $output->writeln('<info>Command halted. Nothing has been done.</info>');
            return false;
        }

        return true;
    }
}
