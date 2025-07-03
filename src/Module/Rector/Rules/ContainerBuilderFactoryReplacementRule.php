<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\OxidEshopUpdateComponent\Module\Rector\Rules;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Use_;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

final class ContainerBuilderFactoryReplacementRule extends AbstractRector
{
    private readonly string $containerBuilderFactoryClass;
    private readonly string $containerBuilderClass;
    private readonly string $basicContextClass;
    private readonly string $shopIdCalculatorClass;
    private readonly string $utilsServerClass;

    public function __construct()
    {
        $this->containerBuilderFactoryClass = 'OxidEsales\EshopCommunity\Internal\Container\ContainerBuilderFactory';
        $this->containerBuilderClass = 'OxidEsales\EshopCommunity\Internal\Framework\DIContainer\ContainerBuilder';
        $this->basicContextClass = 'OxidEsales\EshopCommunity\Internal\Transition\Utility\BasicContext';
        $this->shopIdCalculatorClass = 'OxidEsales\EshopCommunity\Internal\Transition\ShopEvents\ShopIdCalculator';
        $this->utilsServerClass = 'OxidEsales\EshopCommunity\Core\UtilsServer';
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Replaces ContainerBuilderFactory usage with direct ContainerBuilder instantiation',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
use OxidEsales\EshopCommunity\Internal\Container\ContainerBuilderFactory;

$container = (new ContainerBuilderFactory())->create()->getContainer();
$container->compile();
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
use OxidEsales\EshopCommunity\Internal\Framework\DIContainer\ContainerBuilder;
use OxidEsales\EshopCommunity\Internal\Transition\Utility\BasicContext;
use OxidEsales\EshopCommunity\Internal\Transition\ShopEvents\ShopIdCalculator;
use OxidEsales\EshopCommunity\Core\UtilsServer;

$containerBuilder = new ContainerBuilder(new BasicContext(), (new ShopIdCalculator(new UtilsServer()))->getShopId());
$container = $containerBuilder->getContainer();
$container->compile();
CODE_SAMPLE
                )
            ]
        );
    }

    public function getNodeTypes(): array
    {
        return [
            New_::class,
            Use_::class,
            MethodCall::class,
        ];
    }

    public function refactor(Node $node): ?Node
    {
        if ($node instanceof Use_) {
            foreach ($node->uses as $useUse) {
                if ($this->isName($useUse, $this->containerBuilderFactoryClass)) {
                    return null;
                }
            }
        }

        if ($node instanceof MethodCall) {
            if ($this->isName($node->name, 'create') && $node->var instanceof New_) {
                if ($this->isName($node->var->class, $this->containerBuilderFactoryClass)) {
                    return $this->createContainerBuilder();
                }
            }
        }

        if ($node instanceof New_) {
            if (!$this->isName($node->class, $this->containerBuilderFactoryClass)) {
                return null;
            }

            return $this->createContainerBuilder();
        }

        return null;
    }

    private function createContainerBuilder(): New_
    {
        $basicContext = new New_(new FullyQualified($this->basicContextClass));
        $utilsServer = new New_(new FullyQualified($this->utilsServerClass));
        $shopIdCalculator = new New_(new FullyQualified($this->shopIdCalculatorClass), [new Arg($utilsServer)]);
        $shopIdCall = new MethodCall($shopIdCalculator, 'getShopId');

        return new New_(
            new FullyQualified($this->containerBuilderClass),
            [new Arg($basicContext), new Arg($shopIdCall)]
        );
    }
}
