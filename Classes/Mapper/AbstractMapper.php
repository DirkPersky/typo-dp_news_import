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
use GeorgRinger\News\Domain\Repository\CategoryRepository;
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

    /** @var CategoryRepository */
    protected $categoryRepository;

    const TITLE_LENGTH = 125;

    public function __construct()
    {
        $fieldConfig = $GLOBALS['TCA']['tx_news_domain_model_news']['columns']['path_segment']['config'];

        $this->logger = GeneralUtility::makeInstance(LogManager::class)->getLogger(__CLASS__);

        $this->newsRepository = GeneralUtility::makeInstance(NewsRepository::class);
        $this->categoryRepository = GeneralUtility::makeInstance(CategoryRepository::class);

        if (class_exists(SlugHelper::class) === true) {
            $this->slugHelper = GeneralUtility::makeInstance(SlugHelper::class, 'tx_news_domain_model_news', 'path_segment', $fieldConfig);
        }
    }

    protected function findRecord($id, $source)
    {
        return $this->newsRepository->findOneByImportSourceAndImportId($source, $id);
    }
    protected function findCatByName($name)
    {
        $query = $this->categoryRepository->createQuery();
        $query->getQuerySettings()->setRespectStoragePage(false);
        $query->getQuerySettings()->setIgnoreEnableFields(true);

        $result = $query->matching(
            $query->equals('title', $name)
        )->execute();

        return $result->getFirst();
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
        $content = htmlentities(trim($content));
        // remove undisplayable icons
        return nl2br(html_entity_decode(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $content)));
    }

    protected function splitTitle(string $text)
    {
        // split headline from text
        list($title, $text) = array_map('trim', explode("\n", $text, 2));
        // build title & text for to long first line
        if (strlen($title) > static::TITLE_LENGTH) {
            // split after length
            $prepText = substr($title, static::TITLE_LENGTH);
            // find next whitespace for real split
            $count = strpos($prepText, ' ');
            if ($count !== false) {
                // build new PrepText
                $prepText = substr($prepText, $count);
            } else {
                // trim fix for non count
                $count = -1;
            }
            // substr new title
            $title = substr($title, 0, static::TITLE_LENGTH + $count) . '...';
            // build final title
            $title = trim($title) . ($count < 0 ? '...' : '');
            // build main text
            $text = trim($prepText) . "\n" . $text;
        }

        // return to pasing
        return [$title, $text];
    }
}