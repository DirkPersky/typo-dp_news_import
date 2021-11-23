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

namespace DirkPersky\NewsImport\Mapper;

use GeorgRinger\News\Domain\Repository\NewsRepository;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\DataHandling\SlugHelper;
use TYPO3\CMS\Core\Log\Logger;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;

abstract class AbstractMapper
{
    /** @var $logger Logger */
    protected $logger;

    /** @var SlugHelper */
    protected $slugHelper;

    /** @var NewsRepository */
    protected $newsRepository;

    public function __construct()
    {
        $fieldConfig = $GLOBALS['TCA']['tx_news_domain_model_news']['columns']['path_segment']['config'];

        $this->logger = GeneralUtility::makeInstance(LogManager::class)->getLogger(__CLASS__);

        $this->newsRepository = GeneralUtility::makeInstance(NewsRepository::class);

        if (class_exists(SlugHelper::class) === true) {
            $this->slugHelper = GeneralUtility::makeInstance(SlugHelper::class, 'tx_news_domain_model_news', 'path_segment', $fieldConfig);
        }
    }

    protected function findRecord($id, $source)
    {
        return $this->newsRepository->findOneByImportSourceAndImportId($source, $id);
    }

    /**
     * @param string $content
     * @return string
     */
    protected function cleanup($content): string
    {
        if (!$content) return '';
        $search = ['<br />', '<br>', '<br/>', LF . LF];
        $replace = [LF, LF, LF, LF];
        $content = str_replace($search, $replace, $content);
        // trim and add Breaks
        $content = nl2br(trim($content));
        // remove undisplayable icons
        return preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $content);
    }
}