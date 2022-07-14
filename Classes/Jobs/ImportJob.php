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

use DirkPersky\NewsImport\Mapper;
use DirkPersky\NewsImport\Model\TaskConfiguration;
use Exception;
use GeorgRinger\News\Domain\Service\NewsImportService;
use TYPO3\CMS\Core\Log\Logger;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;

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
     * @var NewsImportService
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
     * @throws Exception
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
            case 'xml':
                $mapper = GeneralUtility::makeInstance(Mapper\XMLMapper::class);
                break;
            default:
                $mapper = GeneralUtility::makeInstance(Mapper\JsonMapper::class);
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