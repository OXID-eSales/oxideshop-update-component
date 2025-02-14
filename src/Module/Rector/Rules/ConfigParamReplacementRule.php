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
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Scalar\String_;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

final class ConfigParamReplacementRule extends AbstractRector
{
    private readonly array $parameterMapping;
    private readonly array $databaseMapping;
    private readonly string $containerFacadeClass;
    private array $unmappedParameters = [];

    public function __construct()
    {
        $this->containerFacadeClass = 'OxidEsales\EshopCommunity\Core\Di\ContainerFacade';

        $this->databaseMapping = [
            'dbHost' => 'getHost',
            'dbPort' => 'getPort',
            'dbName' => 'getName',
            'dbUser' => 'getUser',
            'dbPwd' => 'getPass'
        ];

        $this->parameterMapping = [
            'sLogLevel' => 'oxid_esales.log_level',
            'iDebug' => 'oxid_esales.debug_mode',
            'sCompileDir' => 'oxid_esales.build_directory',
            'sShopURL' => 'oxid_esales.shop_url',
            'sSSLShopURL' => 'oxid_esales.shop_url',
            'sAdminSSLURL' => 'oxid_esales.shop_admin_url',
            'sSSLAltImageUrl' => 'oxid_esales.alternative_image_url',
            'sShopDir' => 'oxid_esales.shop_source_directory',
            'sShopLogo' => 'oxid_esales.shop_logo',
            'aAllowedUploadTypes' => 'oxid_esales.allowed_uploaded_types',
            'blSeoLogging' => 'oxid_esales.log_not_seo_urls',
            'blForceSessionStart' => 'oxid_esales.force_session_start',
            'blSessionUseCookies' => 'oxid_esales.cookies_session',
            'aCookieDomains' => 'oxid_esales.cookie_domains',
            'aCookiePaths' => 'oxid_esales.cookie_paths',
            'disallowForceSessionIdInRequest' => 'oxid_esales.disallow_force_session_id',
            'aRobots' => 'oxid_esales.search_engine_list',
            'aTrustedIPs' => 'oxid_esales.trusted_ips',
            'iBasketReservationCleanPerRequest' => 'oxid_esales.basket_reservation_cleanup_rate',
            'aUserComponentNames' => 'oxid_esales.cacheable_user_components',
            'aMultiLangTables' => 'oxid_esales.multilingual_tables',
            'blUseCron' => 'oxid_esales.cron_enabled',
            'blSkipViewUsage' => 'oxid_esales.skip_database_views_usage',
            'blUseRightsRoles' => 'oxid_esales.user_rights_roles_mode',
            'aMultishopArticleFields' => 'oxid_esales.multi_shop_article_fields',
            'blShowUpdateViews' => 'oxid_esales.show_update_views_button',
            'blLogChangesInAdmin' => 'oxid_esales.log_admin_queries',
            'blMallSharedBasket' => 'oxid_esales.mall_shared_basket',
            'blSeoMode' => 'oxid_esales.seo_mode',
            'iCreditRating' => 'oxid_esales.shop_credit_rating',
            'blDemoShop' => 'oxid_esales.demo_shop_mode',
            'iPicCount' => 'oxid_esales.max_product_pictures_count',
            'aMultiShopTables' => 'oxid_esales.multi_shop_tables',
            'aRequireSessionWithParams' => 'oxid_esales.session_init_params'
        ];
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Transforms configuration parameter names to standardized container parameter names',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
$config->getConfigParam("sLogLevel");
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
ContainerFacade::getParameter("oxid_esales.log_level");
CODE_SAMPLE
                )
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

        if (!$this->isName($node->name, 'getConfigParam')) {
            return null;
        }

        $paramName = $this->getParameterName($node);
        if (!$paramName) {
            return null;
        }

        if (isset($this->databaseMapping[$paramName])) {
            $context = new New_(
                new FullyQualified(
                    'OxidEsales\EshopCommunity\Internal\Transition\Utility\BasicContext'
                )
            );

            $databaseConfigurationClass =
                'OxidEsales\EshopCommunity\Internal\Framework\Configuration\DataObject\DatabaseConfiguration';

            $dbConfig = new New_(
                new FullyQualified($databaseConfigurationClass),
                [new Arg(new MethodCall($context, 'getDatabaseUrl'))]
            );

            return new MethodCall($dbConfig, $this->databaseMapping[$paramName]);
        }

        if (isset($this->parameterMapping[$paramName])) {
            return new StaticCall(
                new FullyQualified($this->containerFacadeClass),
                'getParameter',
                [new Arg(new String_($this->parameterMapping[$paramName]))]
            );
        }

        $currentFile = $this->file->getFilePath();
        $this->unmappedParameters[$paramName][] = $currentFile;

        return null;
    }

    private function getParameterName(MethodCall $node): ?string
    {
        if (!isset($node->args[0]) || !$node->args[0]->value instanceof String_) {
            return null;
        }

        return $node->args[0]->value->value;
    }

    public function __destruct()
    {
        $this->printUnmappedParametersReport();
    }
    private function printUnmappedParametersReport(): void
    {
        if (empty($this->unmappedParameters)) {
            return;
        }

        $message = [
            '',
            'MANUAL REVIEW REQUIRED',
            '=====================',
            'Found configuration parameters that need manual review for migration to the new system.',
            '',
            'Please check each occurrence and verify:',
            '• If the parameter is still required',
            '• If there is a matching parameter in the new configuration',
            '• If a custom migration strategy is needed',
            '',
            'Affected files:',
            '---------------'
        ];

        echo implode(PHP_EOL, $message) . PHP_EOL;

        foreach ($this->unmappedParameters as $param => $files) {
            echo sprintf("'%s' in:", $param) . PHP_EOL;
            foreach (array_unique($files) as $file) {
                echo sprintf("  • %s", $file) . PHP_EOL;
            }
            echo PHP_EOL;
        }
    }
}
