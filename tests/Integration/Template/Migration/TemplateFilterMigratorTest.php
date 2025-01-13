<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\OxidEshopUpdateComponent\Tests\Integration\Template\Migration;

use OxidEsales\EshopCommunity\Tests\ContainerTrait;
use OxidEsales\OxidEshopUpdateComponent\Template\Migration\TemplateFilterMigrator;
use OxidEsales\OxidEshopUpdateComponent\Template\Transformer\TwigFilterTransformerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Path;

final class TemplateFilterMigratorTest extends TestCase
{
    use ContainerTrait;

    private string $temporaryOldTemplatePath;

    protected function setUp(): void
    {
        $this->createContainer();
        $this->compileContainer();
        $this->migrator = new TemplateFilterMigrator(
            $this->container->get(TwigFilterTransformerInterface::class)
        );

        $this->temporaryOldTemplatePath = Path::join(sys_get_temp_dir(), 'templates-migration-test');

        parent::setUp();
    }

    protected function tearDown(): void
    {
        $fileSystem = new Filesystem();
        $fileSystem->remove($this->temporaryOldTemplatePath);

        parent::tearDown();
    }

    public function testMigrate(): void
    {
        $mockPath = Path::join(__DIR__, 'Fixtures');

        $fileSystem = new Filesystem();
        $fileSystem->mirror(Path::join($mockPath, 'oldfilter'), $this->temporaryOldTemplatePath);

        $this->migrator->migrate($this->temporaryOldTemplatePath);

        $templateFiles = ['template.html.twig', 'template-subdirectory\template.html.twig'];

        foreach ($templateFiles as $templateFile) {
            $expectedTemplatePath = Path::join($mockPath, 'newfilter', $templateFile);
            $migratedTemplatePath = Path::join($this->temporaryOldTemplatePath, $templateFile);

            $this->assertFileEquals($expectedTemplatePath, $migratedTemplatePath);
        }
    }
}
