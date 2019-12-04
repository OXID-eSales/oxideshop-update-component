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
use Symfony\Component\Console\Style\SymfonyStyle;

final class UserPaymentsDecodingCommand extends Command
{
    use DecodingConfirmationQuestionProvider;

    /**
     * @var DecoderInterface
     */
    private $decoder;

    public function __construct(DecoderInterface $decoder)
    {
        parent::__construct();
        $this->decoder = $decoder;
    }

    /**
     * @inheritdoc
     */
    protected function configure(): void
    {
        $this->setName('oe:oxideshop-update-component:decode-user-payment-values')
            ->setDescription(
                'Decodes all the of values in oxuserpayments table and converts column to text type.'
            );
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output): void
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
    }
}
