<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\OxidEshopUpdateComponent\Module\Rector\Rules;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

final class QueryBuilderRule extends AbstractRector
{
    private array $queryBuilderMethods = [
        'select', 'insert', 'update', 'delete', 'from', 'where',
        'andWhere', 'orWhere', 'values', 'set', 'orderBy',
        'addOrderBy', 'groupBy', 'having', 'setParameters', 'setParameter',
    ];

    private array $writeMethods = ['insert', 'update', 'delete'];
    private array $readMethods = ['select'];

    private array $fetchMethodMapping = [
        'fetch' => 'fetchAssociative',
        'fetchAll' => 'fetchAllAssociative',
        'fetchColumn' => 'fetchOne',
        'fetchArray' => 'fetchAssociative',
        'fetchAssoc' => 'fetchAssociative',
    ];

    private array $queryBuilderVariables = [];

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Rename execute() to executeQuery() or executeStatement() and update fetch methods based on DBAL 3.0',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
$queryBuilder->select('OXID')->from('testtable')->execute()->fetch();
$queryBuilder->select('OXID')->from('testtable')->execute()->fetchAll();
$queryBuilder->insert('testTable')->values(['OXID' => ':OXID'])->execute();
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
$queryBuilder->select('OXID')->from('testtable')->executeQuery()->fetchAssociative();
$queryBuilder->select('OXID')->from('testtable')->executeQuery()->fetchAllAssociative();
$queryBuilder->insert('testTable')->values(['OXID' => ':OXID'])->executeStatement();
CODE_SAMPLE
                ),
            ]
        );
    }

    public function getNodeTypes(): array
    {
        return [MethodCall::class];
    }

    public function refactor(Node $node): ?Node
    {
        if (!$node instanceof MethodCall) {
            return null;
        }

        $methodName = $this->getName($node->name);

        if (isset($this->fetchMethodMapping[$methodName])) {
            $var = $node->var;
            if ($var instanceof MethodCall && $this->isName($var->name, 'execute')) {
                $node->name = new Identifier($this->fetchMethodMapping[$methodName]);
                return $node;
            }
        }

        if (in_array($methodName, $this->queryBuilderMethods, true)) {
            $varName = $this->getVariableName($node);
            if ($varName !== null) {
                if (in_array($methodName, $this->writeMethods, true)) {
                    $this->queryBuilderVariables[$varName] = 'write';
                } elseif (in_array($methodName, $this->readMethods, true)) {
                    $this->queryBuilderVariables[$varName] = 'read';
                }
            }
            return null;
        }

        if ($this->isName($node->name, 'execute')) {
            $varName = $this->getVariableName($node);
            if ($varName !== null && isset($this->queryBuilderVariables[$varName])) {
                if ($this->queryBuilderVariables[$varName] === 'write') {
                    $node->name = new Identifier('executeStatement');
                    return $node;
                }
                if ($this->queryBuilderVariables[$varName] === 'read') {
                    $node->name = new Identifier('executeQuery');
                    return $node;
                }
            }
        }

        return null;
    }

    private function getVariableName(MethodCall $node): ?string
    {
        $currentNode = $node;
        while ($currentNode instanceof MethodCall) {
            $currentNode = $currentNode->var;
        }

        if ($currentNode instanceof Variable) {
            return $this->getName($currentNode);
        }

        return null;
    }
}
