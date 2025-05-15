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
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Name;
use Rector\Contract\PhpParser\Node\StmtsAwareInterface;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use OxidEsales\Eshop\Core\DatabaseProvider;

final class DatabaseRowNumericIndexAccessToArrayValuesRule extends AbstractRector
{
    private string $dbProviderClass = DatabaseProvider::class;
    private array $staticProviderMethodNames = ['getDb', 'getMaster'];
    private string $rowVariableName = 'numericIndexedFields';
    private string $getRowMethodName = 'getRow';
    private array $databaseAssignments = [];

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Insert `$numericIndexedFields = array_values($row);` after assignments from $row = ...->getRow(),
             where $row is later accessed by numeric index.',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
$row = DatabaseProvider::getDb()->getRow($query);
if ($row[1]) { /* ... */ }
$foo = $row[0];
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
$row = DatabaseProvider::getDb()->getRow($query);
$numericIndexedFields = array_values($row);
if ($numericIndexedFields[1]) { /* ... */ }
$foo = $numericIndexedFields[0];
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
        if (!$node instanceof StmtsAwareInterface || $node->stmts === null) {
            return null;
        }

        foreach ($node->stmts as $index => $stmt) {
            if ($this->isDbProviderAssignment($stmt)) {
                $assign = $stmt->expr;

                $variableName = $this->extractVariableName($assign->var);
                if ($variableName) {
                    $this->databaseAssignments[] = $variableName;
                }
            }

            if (!$this->isRowAssignment($stmt)) {
                continue;
            }

            $assign = $stmt->expr;
            $varName = $this->extractVariableName($assign->var);
            if (!$varName) {
                continue;
            }

            if (!$this->isNumericIndexedAccessAfter($node->stmts, $index, $varName)) {
                continue;
            }

            $newAssignExpression = $this->createNumericIndexedFieldsAssign($assign->var);

            $this->rewriteStatementsWithNumericFields(
                $node->stmts,
                $index + 1,
                $varName,
                $this->rowVariableName
            );

            array_splice($node->stmts, $index + 1, 0, [$newAssignExpression]);
            return $node;
        }

        return null;
    }

    private function isDbProviderStaticCallWithAllowedMethod(MethodCall $expr): bool
    {
        return isset($expr->var->class) && $this->getName($expr->var->class) === $this->dbProviderClass
            && in_array($this->getName($expr->var->name), $this->staticProviderMethodNames, true);
    }

    private function isRowAssignment($stmt): bool
    {
        if (!$stmt instanceof Expression || !$stmt->expr instanceof Assign) {
            return false;
        }
        $assign = $stmt->expr;
        $expr = $assign->expr;
        if ($expr instanceof MethodCall) {
            $methodName = $expr->name instanceof Node\Identifier ? $expr->name->toString() : null;
            if ($methodName === $this->getRowMethodName) {
                return $this->isDbProviderStaticCallWithAllowedMethod($expr)
                    || in_array($this->getName($expr->var), $this->databaseAssignments, true);
            }
        }
        return false;
    }

    private function extractVariableName($node): ?string
    {
        return $node instanceof Variable && is_string($node->name)
            ? $node->name
            : null;
    }

    private function isNumericIndexedAccessAfter(array $stmts, int $startIndex, string $varName): bool
    {
        $count = count($stmts);
        for ($i = $startIndex + 1; $i < $count; $i++) {
            if ($this->containsNumericIndexedAccess($stmts[$i], $varName)) {
                return true;
            }
        }
        return false;
    }

    private function containsNumericIndexedAccess(Node $node, string $varName): bool
    {
        $found = false;
        $this->traverseNodeTree($node, function ($node) use ($varName, &$found) {
            if (
                $node instanceof ArrayDimFetch &&
                $node->var instanceof Variable &&
                is_string($node->var->name) &&
                $node->var->name === $varName &&
                $node->dim instanceof LNumber
            ) {
                $found = true;
            }
        });
        return $found;
    }

    private function createNumericIndexedFieldsAssign(Variable $assignedVar): Expression
    {
        $assign = new Assign(
            new Variable($this->rowVariableName),
            new FuncCall(
                new Name('array_values'),
                [new Node\Arg(clone $assignedVar)]
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
        string $oldVarName,
        string $newVarName
    ): void {
        $numStmts = count($stmts);
        for ($i = $startIndex; $i < $numStmts; $i++) {
            $stmts[$i] = $this->replaceNumericIndexAccess(
                $stmts[$i],
                $oldVarName,
                $newVarName
            );
        }
    }

    private function replaceNumericIndexAccess(
        Node $node,
        string $oldVarName,
        string $newVarName
    ): Node {
        $clonedNode = clone $node;
        $this->traverseNodeTree(
            $clonedNode,
            function ($node) use ($oldVarName, $newVarName) {
                if (
                    $node instanceof ArrayDimFetch &&
                    $node->var instanceof Variable &&
                    is_string($node->var->name) &&
                    $node->var->name === $oldVarName &&
                    $node->dim instanceof LNumber
                ) {
                    $node->var = new Variable($newVarName);
                }
            }
        );
        return $clonedNode;
    }

    private function isDbProviderAssignment($stmt): bool
    {
        if (!$stmt instanceof Expression || !$stmt->expr instanceof Assign) {
            return false;
        }

        $assign = $stmt->expr;
        $expr = $assign->expr;

        if ($expr instanceof StaticCall) {
            $className = $this->getName($expr->class);

            return $className === $this->dbProviderClass;
        }

        return false;
    }
}
