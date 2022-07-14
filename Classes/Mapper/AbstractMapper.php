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

use DirkPersky\NewsImport\Model\TaskConfiguration;
use GeorgRinger\News\Domain\Repository\NewsRepository;
use GeorgRinger\News\Domain\Repository\CategoryRepository;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\DataHandling\SlugHelper;
use TYPO3\CMS\Core\Log\Logger;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use Psr\EventDispatcher\EventDispatcherInterface;

abstract class AbstractMapper
{
    /** @var $eventDispatcher EventDispatcherInterface */
    protected $eventDispatcher;

    /** @var $logger Logger */
    protected $logger;

    /** @var SlugHelper */
    protected $slugHelper;

    /** @var NewsRepository */
    protected $newsRepository;

    /** @var CategoryRepository */
    protected $categoryRepository;

    /**
     * Length for Title for Title Split
     */
    const TITLE_LENGTH = 125;

    /**
     *
     */
    public function __construct()
    {
        $fieldConfig = $GLOBALS['TCA']['tx_news_domain_model_news']['columns']['path_segment']['config'];

        $this->logger = GeneralUtility::makeInstance(LogManager::class)->getLogger(__CLASS__);

        $this->newsRepository = GeneralUtility::makeInstance(NewsRepository::class);
        $this->categoryRepository = GeneralUtility::makeInstance(CategoryRepository::class);
        $this->eventDispatcher = GeneralUtility::makeInstance(EventDispatcherInterface::class);


        if (class_exists(SlugHelper::class) === true) {
            $this->slugHelper = GeneralUtility::makeInstance(SlugHelper::class, 'tx_news_domain_model_news', 'path_segment', $fieldConfig);
        }
    }

    /**
     * @param $items
     * @param TaskConfiguration $configuration
     * @return mixed
     */
    protected function prePareItems($items, TaskConfiguration $configuration)
    {
        // loop data and prepare
        foreach ($items as &$item) {
            // check if Already imported and skip if found
            // if ($mapper->findRecord($item['import_id'], $item['import_source']) != null) continue;
            // set default values
            $item = array_merge([
                'import_source' => 'dp_news_import',
                'type' => 0,
                'hidden' => 0,
                'bodytext' => '',
                'title' => '',
                'teaser' => '',
                'pid' => $configuration->getPid(),
                'cruser_id' => $GLOBALS['BE_USER']->user['uid'],
                'crdate' => $GLOBALS['EXEC_TIME'],
                '_dynamicData' => [
                    'import_ref' => $item['import_id']
                ]
            ], $item);
            // get Title
            // extract headline
            if (empty($item['title'])) {
                list($title, $teaser) = $this->splitTitle($item['teaser']);
                $item['title'] = $title;
                $item['teaser'] = $teaser;
            }
            if (empty($item['title'])) {
                list($title, $bodytext) = $this->splitTitle($item['bodytext']);
                $item['title'] = $title;
                $item['bodytext'] = $bodytext;
            }
            // clean fields
            $item['bodytext'] = $this->cleanup($item['bodytext']);
            $item['title'] = $this->cleanup($item['title']);
            $item['teaser'] = $this->cleanup($item['teaser']);
            // set Categories
            if ($item['categories']) $item['categories'] = $this->getCategories($item['categories'], $configuration);
            // Load media
            if ($item['media']) $item['media'] = $this->getMediaFile($item['media'], $item['import_id']);
            // Update Slug
            if ($this->slugHelper) $item['path_segment'] = $this->slugHelper->generate($item, $configuration->getPid());
        }
        return $items;
    }

    /**
     * @param $id
     * @param $source
     * @return mixed
     */
    protected function findRecord($id, $source)
    {
        return $this->newsRepository->findOneByImportSourceAndImportId($source, $id);
    }

    /**
     * @param $name
     * @return mixed
     */
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

    /**
     * @param string $text
     * @return array|string[]
     */
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

    /**
     * @param $url
     * @param $id
     * @return array
     */
    protected function getMediaFile($remoteFile, $id)
    {
        $extensions = [
            'image/jpeg' => 'jpg',
            'image/gif' => 'gif',
            'image/png' => 'png',
            'application/pdf' => 'pdf',
        ];
        // File holder
        $media = [];
        // url exists
        if (!empty($remoteFile)) {
            if (is_array($remoteFile)) {
                // get Mine TYPE
                $mimeType = $remoteFile['mimeType'];
                // file content
                $file_content = $remoteFile['content'];
            } else {
                $ch = curl_init($remoteFile);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                $file_content = curl_exec($ch);
                // get Mine TYPE
                $mimeType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
            }
            // check mine type exists
            if (isset($extensions[$mimeType])) {
                $upload_dir = 'uploads/tx_dp_news_import/';
                // upload path
                $file = $upload_dir . $id . '.' . $extensions[$mimeType];
                // get Path by TYPO3-Version
                if (class_exists(Environment::class)) {
                    $public_path = Environment::getPublicPath() . '/';
                } else {
                    $public_path = PATH_site;
                }
                // check folder exists or create
                if (!is_dir($public_path . $upload_dir)) GeneralUtility::mkdir($public_path . $upload_dir);
                // if file exists
                if (is_file($public_path . $file)) {
                    $status = true;
                } else {
                    $status = GeneralUtility::writeFile($public_path . $file, $file_content);
                }
                // set media def
                if ($status) {
                    $media[] = [
                        'image' => $file,
                        'showinpreview' => true
                    ];
                }
            }
        }

        return $media;
    }

    /**
     * @param $feeds
     * @param TaskConfiguration $configuration
     * @return array
     */
    protected function getCategories($feeds, TaskConfiguration $configuration)
    {
        // ID Holder
        $categoryIds = [];
        // has Cats
        if (!empty($feeds)) {
            if (!$configuration->getMapping()) {
                $this->logger->info('Categories found during import, try to match by name');
                // cast to array for looping
                if (is_string($feeds)) $feeds = [$feeds];
                // loop cats
                foreach ($feeds as $cat) {
                    // find cat by name
                    $category = $this->findCatByName($cat);
                    // map ids to holder
                    if ($category) $categoryIds[] = $category->getUid();
                }
            } else {
                $mapping = $configuration->getMapping();
                // split lines
                $mapping = explode(LF, $mapping);
                // mapping Holder
                $categoryMapping = [];
                // build Mapping Array
                foreach ($mapping as $line) {
                    $_option = array_map('trim', explode(':', $line));
                    $categoryMapping[strtolower($_option[1])] = $_option[0];
                }
                // explode if is string
                if (is_string($feeds)) $feeds = explode(',', $feeds);
                // loop if array
                if (is_array($feeds)) {
                    // loop Cats
                    foreach ($feeds as $feed) {
                        // lower case
                        $title = strtolower($feed);
                        // check if Mapping exists
                        if (!isset($categoryMapping[$title])) {
                            $this->logger->warning(sprintf('Category mapping is missing for category "%s"', $title));
                        } else {
                            // map ids to holder
                            $categoryIds[] = $categoryMapping[$title];
                        }
                    }
                }
            }
        }
        // return mapped IDs
        return $categoryIds;
    }
}