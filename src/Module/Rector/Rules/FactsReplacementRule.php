<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\OxidEshopUpdateComponent\Module\Rector\Rules;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Return_;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

final class FactsReplacementRule extends AbstractRector
{
    private readonly string $editionClass;
    private readonly string $databaseConfigClass;
    private array $enumExpectedMethods = ['getEditionSourcePath'];

    public function __construct()
    {
        $this->editionClass = 'OxidEsales\EshopCommunity\Internal\Framework\Edition\Edition';
        $this->databaseConfigClass =
            'OxidEsales\EshopCommunity\Internal\Framework\Configuration\DataObject\DatabaseConfiguration';
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Transforms Facts methods to new implementation',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
$facts = new Facts();
if ($facts->isEnterprise()) {
    $dbName = $facts->getDatabaseName();
    $path = $facts->getEnterpriseEditionRootPath();
}
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
$context = new BasicContext();
if ($context->getEdition()->value === Edition::Enterprise->value) {
    $dbName = (new DatabaseConfiguration($context->getDatabaseUrl()))->getName();
    $path = $context->getEditionSourcePath(Edition::Enterprise);
}
CODE_SAMPLE
                )
            ]
        );
    }

    public function getNodeTypes(): array
    {
        return [
            MethodCall::class,
            ClassConstFetch::class,
            Return_::class,
            Identical::class,
        ];
    }

    public function refactor(Node $node): ?Node
    {
        if ($node instanceof Return_) {
            return $this->refactorDatabaseConfigGeneration($node);
        }

        if ($node instanceof Identical) {
            return $this->refactorEditionComparison($node);
        }

        if ($node instanceof MethodCall) {
            $methodName = $this->getName($node->name);
            if ($methodName === null) {
                return null;
            }

            if ($node->getAttribute('alreadyTransformed', false)) {
                return null;
            }

            if ($methodName === 'getEdition') {
                $parent = $node->getAttribute('parent');
                if ($parent instanceof PropertyFetch && $this->getName($parent->name) === 'value') {
                    return null;
                }
                $newNode = new PropertyFetch($node, 'value');
                $newNode->setAttribute('alreadyTransformed', true);
                return $newNode;
            }

            return match ($methodName) {
                'isEnterprise' => $this->createEditionComparison($node, 'Enterprise'),
                'isProfessional' => $this->createEditionComparison($node, 'Professional'),
                'isCommunity' => $this->createEditionComparison($node, 'Community'),
                'getDatabaseName',
                'getDatabaseUserName',
                'getDatabasePassword',
                'getDatabaseHost',
                'getDatabasePort' => $this->createDatabaseConfigCall($node, $methodName),
                'getCommunityEditionSourcePath',
                'getCommunityEditionRootPath' => $this->createEditionSourcePathCall($node, 'Community'),
                'getProfessionalEditionRootPath' => $this->createEditionSourcePathCall($node, 'Professional'),
                'getEnterpriseEditionRootPath' => $this->createEditionSourcePathCall($node, 'Enterprise'),
                default => null,
            };
        }

        if ($node instanceof ClassConstFetch && $this->isEditionConstFetch($node)) {
            if (
                $node->getAttribute('alreadyTransformed', false)
                || $node->getAttribute('skipEnumTransformation', false)
            ) {
                return null;
            }

            $parent = $node->getAttribute('parent');
            if ($parent instanceof Arg) {
                $methodCall = $parent->getAttribute('parent');
                if ($methodCall instanceof MethodCall) {
                    $callName = $this->getName($methodCall->name);
                    if (in_array($callName, $this->enumExpectedMethods, true)) {
                        return null;
                    }
                }
            }

            return new PropertyFetch($node, 'value');
        }

        return null;
    }

    private function refactorEditionComparison(Identical $node): ?Node
    {
        if ($node->left instanceof ClassConstFetch && $this->isEditionConstFetch($node->left)) {
            if (
                !($node->left->getAttribute('parent') instanceof PropertyFetch
                && $this->getName($node->left->getAttribute('parent')->name) === 'value')
            ) {
                $node->left = new PropertyFetch($node->left, 'value');
            }
        }

        if ($node->right instanceof ClassConstFetch && $this->isEditionConstFetch($node->right)) {
            if (
                !($node->right->getAttribute('parent') instanceof PropertyFetch
                && $this->getName($node->right->getAttribute('parent')->name) === 'value')
            ) {
                $node->right = new PropertyFetch($node->right, 'value');
            }
        }

        return $node;
    }

    private function isEditionConstFetch(ClassConstFetch $node): bool
    {
        $className = $this->getName($node->class);
        return $className === 'Edition' || $className === $this->editionClass;
    }

    private function createEditionComparison(MethodCall $node, string $edition): Identical
    {
        $getEditionCall = new MethodCall($node->var, 'getEdition');
        $getEditionCall->setAttribute('alreadyTransformed', true);

        $left = new PropertyFetch($getEditionCall, 'value');

        $enumConst = new ClassConstFetch(new FullyQualified($this->editionClass), $edition);
        $enumConst->setAttribute('alreadyTransformed', true);

        $right = new PropertyFetch($enumConst, 'value');

        return new Identical($left, $right);
    }

    private function createDatabaseConfigCall(MethodCall $node, string $methodName): MethodCall
    {
        $newMethodName = str_replace('getDatabase', 'get', $methodName);
        if ($newMethodName === 'getUserName') {
            $newMethodName = 'getUser';
        }
        if ($newMethodName === 'getPassword') {
            $newMethodName = 'getPass';
        }

        $dbConfig = new New_(
            new FullyQualified($this->databaseConfigClass),
            [new Arg(new MethodCall($node->var, 'getDatabaseUrl'))]
        );

        return new MethodCall($dbConfig, $newMethodName);
    }

    private function createEditionSourcePathCall(MethodCall $node, string $edition): MethodCall
    {
        $enumConst = new ClassConstFetch(new FullyQualified($this->editionClass), $edition);
        $enumConst->setAttribute('skipEnumTransformation', true);

        return new MethodCall(
            $node->var,
            'getEditionSourcePath',
            [new Arg($enumConst)]
        );
    }

    private function refactorDatabaseConfigGeneration(Return_ $node): ?Node
    {
        if (!$node->expr instanceof MethodCall) {
            return null;
        }

        $methodCall = $node->expr;
        if ($this->getName($methodCall->name) !== 'generate') {
            return null;
        }

        $basicContext = new New_(
            new FullyQualified('OxidEsales\EshopCommunity\Internal\Transition\Utility\BasicContext')
        );

        $dbConfig = new New_(
            new FullyQualified($this->databaseConfigClass),
            [new Arg(new MethodCall($basicContext, 'getDatabaseUrl'))]
        );

        $newStaticCall = new StaticCall(
            new FullyQualified('OxidEsales\Codeception\Module\Database'),
            'generateStartupOptionsFile',
            [
                new Arg(new MethodCall($dbConfig, 'getUser')),
                new Arg(new MethodCall($dbConfig, 'getPass')),
                new Arg(new MethodCall($dbConfig, 'getHost')),
                new Arg(new MethodCall($dbConfig, 'getPort')),
            ]
        );

        return new Return_($newStaticCall);
    }
}
