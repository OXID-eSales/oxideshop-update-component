<?php declare(strict_types=1);
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\OxidEshopUpdateComponent\Module\Command;

use OxidEsales\EshopCommunity\Internal\Application\Utility\BasicContextInterface;
use OxidEsales\EshopCommunity\Internal\Module\Install\Service\ModuleConfigurationInstallerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

/**
 * Command to install all modules in source/modules directory.
 * @internal
 */
class InstallAllModulesConfigurationCommand extends Command
{
    public const MESSAGE_INSTALLATION_WAS_SUCCESSFUL = 'All module configurations have been installed.';
    public const MESSAGE_INSTALLATION_FAILED = 'An error occurred while installing module configurations.';

    /**
     * @var ModuleConfigurationInstallerInterface
     */
    private $moduleConfigurationInstaller;

    /**
     * @var BasicContextInterface
     */
    private $context;

    /**
     * @var Finder
     */
    private $finder;

    /**
     * @param ModuleConfigurationInstallerInterface $moduleConfigurationInstaller
     * @param BasicContextInterface $context
     * @param Finder $finder
     */
    public function __construct(
        ModuleConfigurationInstallerInterface $moduleConfigurationInstaller,
        BasicContextInterface $context,
        Finder $finder
    ) {
        $this->moduleConfigurationInstaller = $moduleConfigurationInstaller;
        $this->context = $context;
        $this->finder = $finder;

        parent::__construct();
    }

    /**
     * @inheritdoc
     */
    protected function configure(): void
    {
        $this
            ->setName(
                'oe:oxideshop-update-component:install-all-modules'
            )
            ->setDescription(
                'Install all modules that inside source/modules directory'
            );
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @throws \Throwable
     */
    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        try {
            $moduleDirectory = $this->context->getModulesPath();
            $moduleDirectories = $this->finder->files()->name('metadata.php')->in($moduleDirectory);

            foreach ($moduleDirectories->getIterator() as $directory) {
                $output->writeln('<info>' . 'installing module' . $directory->getPath() . '</info>');
                $this->moduleConfigurationInstaller->install($directory->getPath(), $directory->getPath());
            }

            $output->writeln('<info>' . self::MESSAGE_INSTALLATION_WAS_SUCCESSFUL . '</info>');
        } catch (\Throwable $throwable) {
            $output->writeln('<error>' . self::MESSAGE_INSTALLATION_FAILED . '</error>');

            throw $throwable;
        }
    }
}
