<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\OxidEshopUpdateComponent\Module\Rector\Rules;

use OxidEsales\Eshop\Core\Database\Adapter\DatabaseInterface;
use OxidEsales\Eshop\Core\DatabaseProvider;
use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Expr\Variable;
use PhpParser\NodeVisitor;
use PHPStan\Type\ObjectType;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

final class DatabaseRemoveSetFetchModeRule extends AbstractRector
{
    private array $dbVars = [];

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Removes setFetchMode method calls as they are no longer needed',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
$database = \OxidEsales\Eshop\Core\DatabaseProvider::getDb();
$database->setFetchMode(\OxidEsales\Eshop\Core\Database\Adapter\DatabaseInterface::FETCH_MODE_ASSOC);
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
$database = \OxidEsales\Eshop\Core\DatabaseProvider::getDb();

CODE_SAMPLE
                )
            ]
        );
    }

    public function getNodeTypes(): array
    {
        return [Assign::class, Expression::class];
    }

    public function refactor(Node $node): ?int
    {
        if (
            $node instanceof Assign
            && $node->var instanceof Variable
            && $node->expr instanceof StaticCall
            && $this->isName($node->expr->class, DatabaseProvider::class)
            && $this->isNames($node->expr->name, ['getDb', 'getMaster'])
        ) {
            $varName = $this->getName($node->var);
            if ($varName) {
                $this->dbVars[] = $varName;
            }
        }

        if ($node instanceof Expression && $node->expr instanceof MethodCall) {
            $methodCall = $node->expr;

            if (
                $methodCall->var instanceof Variable
                && $this->isName($methodCall->name, 'setFetchMode')
            ) {
                $varName = $this->getName($methodCall->var);

                if (
                    $varName
                    && (
                        in_array($varName, $this->dbVars, true)
                        || $this->isObjectType($methodCall->var, new ObjectType(DatabaseInterface::class))
                    )
                ) {
                    return NodeVisitor::REMOVE_NODE;
                }
            }
        }

        return null;
    }
}
