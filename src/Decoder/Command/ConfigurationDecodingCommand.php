<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\OxidEshopUpdateComponent\Decoder\Command;

use OxidEsales\OxidEshopUpdateComponent\Decoder\Service\DecoderInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

/**
 * @internal
 */
final class ConfigurationDecodingCommand extends Command
{
    /**
     * @var DecoderInterface
     */
    private $decoder;

    public function __construct(
        DecoderInterface $decoder
    ) {
        parent::__construct();
        $this->decoder = $decoder;
    }

    /**
     * @inheritdoc
     */
    protected function configure(): void
    {
        $this->setName('oe:oxideshop-update-component:decode-config-values');
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        if ($this->userConfirmation($input, $output)) {
            $this->decoder->decode();
        }
    }

    private function userConfirmation(InputInterface $input, OutputInterface $output): bool
    {
        $question = new ConfirmationQuestion(
            '<question>All of the config table values will be decoded without possibility to revert.'
                . PHP_EOL . 'We recommend before running this command to make database backup?'
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
