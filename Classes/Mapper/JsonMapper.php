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

use DateTime;
use DirkPersky\NewsImport\Model\TaskConfiguration;
use Exception;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class JsonMapper extends AbstractMapper implements MapperInterface
{

    public function execute(TaskConfiguration $configuration)
    {
        $data = file_get_contents($configuration->getPath());
        if (empty($data)) {
            throw new Exception('Empty Response from Source');
        }
        // encode
        $data = json_decode($data);
        if (empty($data->{'data'})) {
            throw new Exception('Empty Data-Object from Source');
        }
        // Item holder
        $items = [];
        // loop data and prepare Mapping
        foreach ($data->{'data'} as $item) {
            $id = strlen($item->{'ref_id'}) > 100 ? md5($item->{'ref_id'}) : $item->{'ref_id'};
            // check if Already imported and skip if found
            if ($this->findRecord($id, 'dp_news_import') != null) continue;
            // get Title
            $title = $item->{'title'};
            $teaser = $item->{'teaser'};
            $detail = $item->{'detail'};
            // extract headline
            if (empty($title)) list($title, $teaser) = explode("\n", $teaser, 2);
            if (empty($title)) list($title, $detail) = explode("\n", $detail, 2);
            // import Data set
            $singleItem = [
                'import_id' => $id,
                'import_source' => 'dp_news_import',

                'pid' => $configuration->getPid(),
                'type' => 0,
                'hidden' => 0,
                'cruser_id' => $GLOBALS['BE_USER']->user['uid'],
                'crdate' => $GLOBALS['EXEC_TIME'],

                'title' => $this->cleanup($title),
                'teaser' => $this->cleanup($teaser),
                'bodytext' => $this->cleanup($detail),
                'media' => $this->getMediaFile($item->{'media_url'}, $item->{'ref_id'}),

                'datetime' => (new DateTime($item->{'created_at'}))->getTimestamp(),
                'categories' => $this->getCategories($item->{'feed'}, $configuration),

                '_dynamicData' => [
                    'import_ref' => $item->{'ref_id'},
                    'import_data' => json_encode($item),
                ]
            ];
            // mark as External
            if (isset($item->{'url'}) && !empty($item->{'url'})) {
                $singleItem['type'] = 2;
                $singleItem['externalurl'] = $item->{'url'};
            }
            // Update Slug
            if ($configuration->isSetSlug() && $this->slugHelper) {
                $singleItem['path_segment'] = $this->slugHelper->generate($singleItem, $configuration->getPid());
            }
            // add to holder
            $items[] = $singleItem;
        }
        // return news set
        return $items;
    }

    /**
     * @param $url
     * @param $id
     * @return array
     */
    protected function getMediaFile($url, $id)
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
        if (!empty($url)) {
            // get File
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $file_content = curl_exec($ch);
            // get Mine TYPE
            $mimeType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
            // check mine type exists
            if (isset($extensions[$mimeType])) {
                $upload_dir = 'uploads/tx_dp_news_import/';
                // upload path
                $file = $upload_dir . $id . '_' . md5($url) . '.' . $extensions[$mimeType];
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
                $this->logger->info('Categories found during import but no mapping assigned in the task!');
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