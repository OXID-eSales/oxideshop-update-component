<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\OxidEshopUpdateComponent\Module\Rector\Rules;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Name;
use PHPStan\Type\ObjectType;
use Rector\Contract\PhpParser\Node\StmtsAwareInterface;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use OxidEsales\Eshop\Core\DatabaseProvider;
use OxidEsales\Eshop\Core\Database\Adapter\DatabaseInterface;

final class DatabaseNumericFieldAccessToStringKeyRule extends AbstractRector
{
    private array $selectMethodNames = ['select', 'selectLimit'];
    private array $staticProviderMethodNames = ['getDb', 'getMaster'];

    private string $databaseInterfaceClass = DatabaseInterface::class;
    private string $databaseProviderClass = DatabaseProvider::class;
    private string $fieldsProperty = 'fields';

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Insert `$numericIndexedFields = array_values($result->fields);` after database select assignments, ' .
            'but only if $result->fields is later accessed via numeric index.',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
$result = DatabaseProvider::getDb()->select($query, $params);
if ($result->fields[1]) { echo "foo"; }
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
$result = DatabaseProvider::getDb()->select($query, $params);
$numericIndexedFields = array_values($result->fields);
if ($result->fields[1]) { echo "foo"; }
CODE_SAMPLE
                ),
            ]
        );
    }

    public function getNodeTypes(): array
    {
        return [StmtsAwareInterface::class];
    }

    public function refactor(Node $node): ?Node
    {
        if (!$this->isNodeWithStatements($node)) {
            return null;
        }

        foreach ($node->stmts as $assignIndex => $stmt) {
            if (!$this->shouldProcessAssignment($stmt)) {
                continue;
            }

            [$assignedVar, $assignExpr] = $this->extractAssignedVarAndExpr($stmt);

            if (
                !$this->isSupportedDatabaseSelectAssignment($assignExpr) ||
                !$this->isFromSupportedDatabaseProvider($assignExpr)
            ) {
                continue;
            }

            $resultVarName = $this->extractVariableName($assignedVar);
            if (!$resultVarName) {
                continue;
            }
            $numericIndexedFieldsVarName = $resultVarName . 'NumericIndexedFields';

            if (
                $this->insertBeforeFirstFieldsUsage(
                    $node->stmts,
                    $assignIndex + 1,
                    $assignedVar,
                    $resultVarName,
                    $numericIndexedFieldsVarName
                )
            ) {
                $this->rewriteStatementsWithNumericFields(
                    $node->stmts,
                    0,
                    $resultVarName,
                    $numericIndexedFieldsVarName,
                    $this->fieldsProperty
                );
                return $node;
            }
        }

        return null;
    }

    private function containsNumericIndexedFieldsAccess(Node $node, string $varName): bool
    {
        $found = false;
        $this->traverseNodeTree($node, function ($node) use ($varName, &$found) {
            if ($this->isNumericIndexedPropertyAccess($node, $varName)) {
                $found = true;
            }
        });
        return $found;
    }

    private function isNumericIndexedPropertyAccess($node, string $varName): bool
    {
        return $node instanceof ArrayDimFetch
            && $node->var instanceof PropertyFetch
            && $node->var->name instanceof Node\Identifier
            && $node->var->name->toString() === $this->fieldsProperty
            && $node->var->var instanceof Variable
            && is_string($node->var->var->name)
            && $node->var->var->name === $varName
            && $node->dim instanceof LNumber;
    }

    private function insertBeforeFirstFieldsUsage(
        array &$stmts,
        int $startIndex,
        Variable $assignedVar,
        string $resultVarName,
        string $numericIndexedFieldsVarName
    ): bool {
        for ($i = $startIndex, $len = count($stmts); $i < $len; ++$i) {
            $stmt = $stmts[$i];

            if (
                ($stmt instanceof Node\Stmt\If_ || $stmt instanceof Node\Stmt\While_ || $stmt instanceof Node\Stmt\For_)
                && $this->containsNumericIndexedFieldsAccess($stmt->cond, $resultVarName)
            ) {
                $newAssignExpression = $this->createNumericIndexedFieldsAssign(
                    $assignedVar,
                    $numericIndexedFieldsVarName
                );
                array_splice($stmts, $i, 0, [$newAssignExpression]);
                return true;
            }

            if ($this->containsNumericIndexedFieldsAccess($stmt, $resultVarName)) {
                if (
                    $stmt instanceof Node\Stmt\If_
                    || $stmt instanceof Node\Stmt\While_
                    || $stmt instanceof Node\Stmt\Foreach_
                    || $stmt instanceof Node\Stmt\For_
                ) {
                    if (
                        $this->insertBeforeFirstFieldsUsage(
                            $stmt->stmts,
                            0,
                            $assignedVar,
                            $resultVarName,
                            $numericIndexedFieldsVarName
                        )
                    ) {
                        return true;
                    }
                } else {
                    $newAssignExpression = $this->createNumericIndexedFieldsAssign(
                        $assignedVar,
                        $numericIndexedFieldsVarName
                    );
                    array_splice($stmts, $i, 0, [$newAssignExpression]);
                    return true;
                }
            }

            if ($stmt instanceof Node\Stmt\If_) {
                if (
                    $this->insertBeforeFirstFieldsUsage(
                        $stmt->stmts,
                        0,
                        $assignedVar,
                        $resultVarName,
                        $numericIndexedFieldsVarName
                    )
                ) {
                    return true;
                }
                if (
                    $stmt->else !== null
                    && $this->insertBeforeFirstFieldsUsage(
                        $stmt->else->stmts,
                        0,
                        $assignedVar,
                        $resultVarName,
                        $numericIndexedFieldsVarName
                    )
                ) {
                    return true;
                }
                foreach ($stmt->elseifs as $elseif) {
                    if (
                        $this->insertBeforeFirstFieldsUsage(
                            $elseif->stmts,
                            0,
                            $assignedVar,
                            $resultVarName,
                            $numericIndexedFieldsVarName
                        )
                    ) {
                        return true;
                    }
                }
            }
            if (
                $stmt instanceof Node\Stmt\While_
                || $stmt instanceof Node\Stmt\Foreach_
                || $stmt instanceof Node\Stmt\For_
            ) {
                if (
                    $this->insertBeforeFirstFieldsUsage(
                        $stmt->stmts,
                        0,
                        $assignedVar,
                        $resultVarName,
                        $numericIndexedFieldsVarName
                    )
                ) {
                    return true;
                }
            }
        }
        return false;
    }

    private function isNodeWithStatements($node): bool
    {
        return $node instanceof StmtsAwareInterface && $node->stmts !== null;
    }

    private function shouldProcessAssignment($stmt): bool
    {
        return $stmt instanceof Expression && $stmt->expr instanceof Assign;
    }

    private function extractAssignedVarAndExpr(Expression $stmt): array
    {
        $assign = $stmt->expr;
        return [$assign->var, $assign->expr];
    }

    private function isSupportedDatabaseSelectAssignment($expr): bool
    {
        return $expr instanceof MethodCall
            && in_array($this->getName($expr->name), $this->selectMethodNames, true);
    }

    private function isFromSupportedDatabaseProvider(MethodCall $expr): bool
    {
        $caller = $expr->var;
        return $this->isObjectType($caller, new ObjectType($this->databaseInterfaceClass))
            || $this->isStaticDatabaseProvider($caller);
    }

    private function isStaticDatabaseProvider($caller): bool
    {
        return $caller instanceof StaticCall
            && $this->getName($caller->class) === $this->databaseProviderClass
            && in_array($this->getName($caller->name), $this->staticProviderMethodNames, true);
    }

    private function extractVariableName($node): ?string
    {
        return $node instanceof Variable && is_string($node->name)
            ? $node->name
            : null;
    }

    private function createNumericIndexedFieldsAssign($assignedVar, string $numericIndexedFieldsVarName): Expression
    {
        $assign = new Assign(
            new Variable($numericIndexedFieldsVarName),
            new FuncCall(
                new Name('array_values'),
                [new Node\Arg(new PropertyFetch($assignedVar, $this->fieldsProperty))]
            )
        );

        return new Expression($assign);
    }

    private function traverseNodeTree(Node $node, callable $callback): void
    {
        $callback($node);
        foreach ($node->getSubNodeNames() as $name) {
            $subNode = $node->$name;
            if ($subNode instanceof Node) {
                $this->traverseNodeTree($subNode, $callback);
            } elseif (is_array($subNode)) {
                foreach ($subNode as $item) {
                    if ($item instanceof Node) {
                        $this->traverseNodeTree($item, $callback);
                    }
                }
            }
        }
    }

    private function rewriteStatementsWithNumericFields(
        array &$stmts,
        int $startIndex,
        string $resultVarName,
        string $numericVarName,
        string $fieldsPropertyName
    ): void {
        $numStmts = count($stmts);
        for ($i = $startIndex; $i < $numStmts; $i++) {
            $stmts[$i] = $this->replaceFieldsAccessWithNumericFields(
                $stmts[$i],
                $resultVarName,
                $numericVarName,
                $fieldsPropertyName
            );
        }
    }

    private function replaceFieldsAccessWithNumericFields(
        Node $node,
        string $resultVarName,
        string $numericVarName,
        string $fieldsPropertyName
    ): Node {
        $clonedNode = clone $node;
        $this->traverseNodeTree(
            $clonedNode,
            function ($node) use ($resultVarName, $numericVarName, $fieldsPropertyName) {
                if (
                    $node instanceof ArrayDimFetch &&
                    $node->dim instanceof LNumber &&
                    $node->var instanceof PropertyFetch &&
                    $node->var->var instanceof Variable &&
                    $node->var->name instanceof Node\Identifier &&
                    is_string($node->var->var->name) &&
                    $node->var->var->name === $resultVarName &&
                    $node->var->name->toString() === $fieldsPropertyName
                ) {
                    $node->var = new Variable($numericVarName);
                }
            }
        );
        return $clonedNode;
    }
}
