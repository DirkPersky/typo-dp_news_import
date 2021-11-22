<?php
/*
 * Copyright (c) 2021.
 *
 * @category   Shopware
 *
 * @copyright  2020 Dirk Persky (https://github.com/DirkPersky)
 * @author     Dirk Persky <dirk.persky@gmail.com>
 * @license     AGPL
 */

declare(strict_types=1);

namespace DirkPersky\NewsImport\Jobs;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Log\Logger;
use TYPO3\CMS\Core\Log\LogManager;
use GeorgRinger\News\Domain\Service\NewsImportService;
use DirkPersky\NewsImport\Model\TaskConfiguration;
use DirkPersky\NewsImport\Mapper;

class ImportJob
{

    /**
     * @var TaskConfiguration
     */
    protected $configuration;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var \GeorgRinger\News\Domain\Service\NewsImportService
     */
    protected $newsImportService;

    /**
     * ImportJob constructor.
     * @param TaskConfiguration $configuration
     * @param NewsImportService $newsImportService
     */
    public function __construct(
        TaskConfiguration $configuration,
        NewsImportService $newsImportService
    )
    {
        $this->logger = GeneralUtility::makeInstance(LogManager::class)->getLogger(__CLASS__);
        $this->configuration = $configuration;
        $this->newsImportService = $newsImportService;
    }

    /**
     * Import remote content
     */
    public function run()
    {
        // log start message
        $this->logger->info(sprintf(
            'Starting import of "%s" (%s)',
            $this->configuration->getPath(),
            strtoupper($this->configuration->getFormat())
        ));
        // init Mapper by Type an run import
        switch (strtolower($this->configuration->getFormat())) {
            default:
                $mapper = new Mapper\JsonMapper();
        }
        // log import result
        $this->import($mapper->execute($this->configuration));
    }

    /**
     * @param array|null $data
     */
    protected function import(array $data = null)
    {
        $this->logger->info(sprintf('Starting import of %s records', count($data)));
        // import data
        $this->newsImportService->import($data);
    }
}