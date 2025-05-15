<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\OxidEshopUpdateComponent\Module\Rector\Rules;

use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Name;
use PhpParser\Node\Param;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use OxidEsales\Eshop\Core\Database\Adapter\DatabaseInterface;
use OxidEsales\Eshop\Core\DatabaseProvider;

final class DatabaseParameterPrefixRule extends AbstractRector
{
    private string $databaseInterface = DatabaseInterface::class;
    private string $databaseProviderClass = DatabaseProvider::class;
    private array $databaseProviderMethods = ['getDb', 'getMaster', 'createDatabase'];
    private array $databaseMethods = ['execute', 'select', 'getOne', 'getRow', 'getAll'];

    private array $isDatabaseVariable = [];

    private array $assignedArrays = [];

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Removes colon prefix from parameter names in arrays passed to DatabaseInterface
             methods after getting db instance from provider or assigning DatabaseInterface instance',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
/** @var DatabaseInterface $database */
$database = DatabaseProvider::getDb();
$parameters = [
    ':oxactionid' => $soxId
];
$database->execute($query, $parameters);

/** @var DatabaseInterface $database2 */
$database2 = new Database();
$database2->select($q, $parameters);
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
/** @var DatabaseInterface $database */
$database = DatabaseProvider::getDb();
$parameters = [
    'oxactionid' => $soxId
];
$database->execute($query, $parameters);

/** @var DatabaseInterface $database2 */
$database2 = new Database();
$database2->select($q, $parameters);
CODE_SAMPLE
                )
            ]
        );
    }

    public function getNodeTypes(): array
    {
        return [Assign::class, MethodCall::class, Param::class];
    }

    public function refactor(Node $node): ?Node
    {
        if ($node instanceof Assign) {
            $varName = $this->getName($node->var);

            if ($node->expr instanceof Array_ && is_string($varName)) {
                $this->assignedArrays[$varName] = $node->expr;
            }

            if ($node->expr instanceof StaticCall) {
                $class = $this->getName($node->expr->class);
                $method = $this->getName($node->expr->name);
                if (
                    $class === $this->databaseProviderClass
                    && is_string($method)
                    && is_string($varName)
                    && in_array($method, $this->databaseProviderMethods, true)
                ) {
                    $this->isDatabaseVariable[$varName] = true;
                }
            }

            return null;
        }

        if ($node instanceof Param) {
            $varName = $this->getName($node->var);
            if (
                is_string($varName)
                && $node->type instanceof Name
                && $this->getName($node->type) === $this->databaseInterface
            ) {
                $this->isDatabaseVariable[$varName] = true;
            }
            return null;
        }

        if ($node instanceof MethodCall) {
            $methodName = $this->getName($node->name);

            if (!is_string($methodName) || !in_array($methodName, $this->databaseMethods, true)) {
                return null;
            }

            $onVariable = $node->var;
            if ($onVariable instanceof Variable) {
                $databaseVarName = $this->getName($onVariable);
                if ($databaseVarName === null || !isset($this->isDatabaseVariable[$databaseVarName])) {
                    return null;
                }
                foreach ($node->args as $arg) {
                    if ($arg->value instanceof Variable) {
                        $argVarName = $this->getName($arg->value);
                        if ($argVarName !== null && isset($this->assignedArrays[$argVarName])) {
                            $this->removeColonFromArrayKeys($this->assignedArrays[$argVarName]);
                        }
                    } elseif ($arg->value instanceof Array_) {
                        $this->removeColonFromArrayKeys($arg->value);
                    }
                }
            }
        }

        return null;
    }

    private function removeColonFromArrayKeys(Array_ $arrayNode): void
    {
        foreach ($arrayNode->items as $item) {
            if (
                $item instanceof ArrayItem
                && $item->key !== null
                && is_string($item->key->value)
            ) {
                $keyValue = $item->key->value;
                if (\str_starts_with($keyValue, ':')) {
                    $item->key->value = \substr($keyValue, 1);
                }
            }
        }
    }
}
