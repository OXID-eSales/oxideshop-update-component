<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\OxidEshopUpdateComponent\Module\Rector\Rules;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PHPStan\Type\ObjectType;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

final class TransactionConnectionRule extends AbstractRector
{
    private readonly array $validMethods;
    private readonly array $methodMapping;
    private readonly string $transactionServiceInterface;

    public function __construct()
    {
        $this->validMethods = [
            'begin',
            'beginTransaction',
            'commit',
            'rollback',
            'rollBack',
        ];

        $this->methodMapping = [
            'begin' => 'beginTransaction',
            'rollback' => 'rollBack',
        ];

        $this->transactionServiceInterface =
            'OxidEsales\EshopCommunity\Internal\Framework\Database\TransactionServiceInterface';
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Replaces deprecated TransactionService method calls with Connection object calls and ' .
            'injects Connection creation at the method start',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
$transaction->beginTransaction();
try {
    // perform operations
} catch (Exception $e) {
    $transaction->rollback();
    throw new Exception();
}
$transaction->commit();
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
$connection = $transaction->create();
$connection->beginTransaction();
try {
    // perform operations
} catch (Exception $e) {
    $connection->rollBack();
    throw new Exception();
}
$connection->commit();
CODE_SAMPLE
                )
            ]
        );
    }

    public function getNodeTypes(): array
    {
        return [ClassMethod::class];
    }

    public function refactor(Node $node): ?Node
    {
        if (!$node instanceof ClassMethod || $node->stmts === null) {
            return null;
        }

        $hasTransactionCall = false;
        $transactionServiceExpr = null;

        $this->traverseNodesWithCallable($node, function (Node $subNode) use (
            &$hasTransactionCall,
            &$transactionServiceExpr
        ): ?Node {
            if ($subNode instanceof MethodCall) {
                $name = $this->getName($subNode->name);
                if (in_array($name, $this->validMethods, true) && $this->isTransactionServiceCall($subNode)) {
                    $hasTransactionCall = true;
                    if ($transactionServiceExpr === null) {
                        $transactionServiceExpr = $subNode->var;
                    }
                }
            }
            return null;
        });

        if (!$hasTransactionCall || $transactionServiceExpr === null) {
            return null;
        }

        $connectionAssign = new Expression(
            new Assign(
                new Variable('connection'),
                new MethodCall($transactionServiceExpr, 'create')
            )
        );

        array_unshift($node->stmts, $connectionAssign);

        $this->traverseNodesWithCallable($node, function (Node $subNode): ?Node {
            if (!$subNode instanceof MethodCall) {
                return null;
            }

            $name = $this->getName($subNode->name);
            if (!in_array($name, $this->validMethods, true)) {
                return null;
            }

            if (!$this->isTransactionServiceCall($subNode)) {
                return null;
            }

            $newMethodName = $this->methodMapping[$name] ?? $name;
            return new MethodCall(new Variable('connection'), $newMethodName, $subNode->args);
        });

        return $node;
    }

    private function isTransactionServiceCall(MethodCall $methodCall): bool
    {
        return $this->isObjectType(
            $methodCall->var,
            new ObjectType($this->transactionServiceInterface)
        );
    }
}
