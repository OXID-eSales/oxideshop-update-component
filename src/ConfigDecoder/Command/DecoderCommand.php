<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\OxidEshopUpdateComponent\ConfigDecoder\Command;

use OxidEsales\OxidEshopUpdateComponent\ConfigDecoder\Service\DecoderInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @internal
 */
final class DecoderCommand extends Command
{
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
        $this->setName('oe:oxideshop-update-component:decode-config-values');
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $this->decoder->decode();
    }
}
