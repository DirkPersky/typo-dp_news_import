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

namespace DirkPersky\NewsImport\Tasks;


use DirkPersky\NewsImport\Jobs\ImportJob;
use DirkPersky\NewsImport\Model\TaskConfiguration;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Scheduler\Task\AbstractTask;

/**
 * Provides Import task
 */
class ImportTask extends AbstractTask
{
    /** @var string */
    public $format;

    /** @var string */
    public $path;

    /** @var int */
    public $pid;

    /** @var string */
    public $mapping;

    /** @var int */
    public $setSlug;


    public function execute()
    {
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);

        $importJob = $objectManager->get(ImportJob::class, $this->createConfiguration());
        $importJob->run();

        return true;
    }

    /**
     * This method returns additional information about the specific task
     *
     * @return string Information to display
     */
    public function getAdditionalInformation()
    {
        return sprintf('%s: %s,' . LF . ' %s: %s ' . LF . '%s: %s',
            $this->translate('format'), strtoupper($this->format),
            $this->translate('path'), GeneralUtility::fixed_lgd_cs($this->path, 200),
            $this->translate('pid'), $this->pid);
    }

    /**
     * @return TaskConfiguration
     */
    protected function createConfiguration(): TaskConfiguration
    {
        $configuration = new TaskConfiguration();
        $configuration->setPath($this->path);
        $configuration->setMapping($this->mapping);
        $configuration->setFormat($this->format);
        $configuration->setPid((int)$this->pid);
        $configuration->setSetSlug((bool)$this->setSlug);

        return $configuration;
    }

    /**
     * @param string $key
     * @return string
     */
    protected function translate(string $key): string
    {
        /** @var LanguageService $languageService */
        $languageService = $GLOBALS['LANG'];
        return $languageService->sL('LLL:EXT:dp_news_import/Resources/Private/Language/locallang.xlf:scheduler.' . $key);
    }
}