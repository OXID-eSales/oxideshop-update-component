<?php declare(strict_types=1);
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\OxidEshopUpdateComponent\Module\Command;

use OxidEsales\EshopCommunity\Internal\Transition\Utility\BasicContextInterface;
use OxidEsales\EshopCommunity\Internal\Framework\FileSystem\FinderFactoryInterface;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Install\Service\ModuleConfigurationInstallerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

/**
 * Command to install all modules in source/modules directory.
 * @internal
 */
final class InstallAllModulesConfigurationCommand extends Command
{
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
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param ModuleConfigurationInstallerInterface $moduleConfigurationInstaller
     * @param BasicContextInterface $context
     * @param FinderFactoryInterface $finderFactory
     * @param LoggerInterface $logger
     */
    public function __construct(
        ModuleConfigurationInstallerInterface $moduleConfigurationInstaller,
        BasicContextInterface $context,
        FinderFactoryInterface $finderFactory,
        LoggerInterface $logger
    ) {
        $this->moduleConfigurationInstaller = $moduleConfigurationInstaller;
        $this->context = $context;
        $this->finder = $finderFactory->create();
        $this->logger = $logger;

        parent::__construct();
    }

    /**
     * @inheritdoc
     */
    protected function configure(): void
    {
        $this
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
        $moduleDirectory = $this->context->getModulesPath();
        $moduleDirectories = $this->finder->files()->name('metadata.php')->in($moduleDirectory);

        foreach ($moduleDirectories->getIterator() as $directory) {
            $output->writeln('<info>' . 'Installing module ' . $directory->getPath() . '</info>');
            try {
                $this->moduleConfigurationInstaller->install($directory->getPath(), $directory->getPath());
            } catch (\Throwable $throwable) {
                $output->writeln('<error>Module directory of ' .
                                 $directory->getPath() .
                                 '  could not be installed due to ' . $throwable->getMessage() . '</error>');
                $this->logger->error('Module directory of ' .
                                     $directory->getPath() .
                                     '  could not be installed due to ' . $throwable->getMessage(), [$throwable]);
            }
        }

        $output->writeln('<info> All module configurations have been installed. </info>');
    }
}
