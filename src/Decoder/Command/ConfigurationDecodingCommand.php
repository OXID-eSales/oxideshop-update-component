<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\OxidEshopUpdateComponent\Decoder\Command;

use OxidEsales\OxidEshopUpdateComponent\Decoder\Exception\WrongColumnType;
use OxidEsales\OxidEshopUpdateComponent\Decoder\Service\DecoderInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;

final class ConfigurationDecodingCommand extends Command
{
    /**
     * @var DecoderInterface
     */
    private DecoderInterface $decoder;

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
        $this->setDescription(
            'Decodes all of the values in oxconfig table and converts column to text type.'
        );
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($this->userConfirmation($input, $output)) {
            $outputStyled = (new SymfonyStyle($input, $output));
            try {
                $this->decoder->decode();
                $outputStyled->success('Values were decoded successfully!');
            } catch (WrongColumnType $exception) {
                $outputStyled->error($exception->getMessage());
            }
        }

        return 0;
    }

    private function userConfirmation(InputInterface $input, OutputInterface $output): bool
    {
        $question = new ConfirmationQuestion(
            '<question>The column oxvarvalue of the table oxconfig will be decoded without the possibility to revert.'
            . PHP_EOL . 'We recommend to create a database backup before running this command.'
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
