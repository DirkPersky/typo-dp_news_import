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

class XMLMapper extends AbstractMapper implements MapperInterface
{

    public function execute(TaskConfiguration $configuration)
    {
        $data = file_get_contents($configuration->getPath());
        if (empty($data)) {
            throw new Exception('Empty Response from Source');
        }
        // convert XML
        $xml = simplexml_load_string($data, "SimpleXMLElement", LIBXML_NOCDATA);
        $array = json_decode(json_encode((array)$xml), true);
        $array = array($xml->getName() => $array);
        // Item holder
        $items = [];
        // loop Events
        foreach ($array['iCalendar']['vcalendar']['vevent'] as $item) {
            if(!in_array($item['class'],  ['PUBLIC'])) continue;
            // check if Already imported and skip if found
//            if ($this->findRecord($item['uid'], 'dp_news_import') != null) continue;
            // import Data set
            $singleItem = [
                'import_id' => $item['uid'],
                'import_source' => 'dp_news_import',

                'pid' => $configuration->getPid(),
                'type' => 0,
                'hidden' => 0,
                'is_event' => 1,
                'cruser_id' => $GLOBALS['BE_USER']->user['uid'],
                'crdate' => $GLOBALS['EXEC_TIME'],

                'title' => $this->cleanup($item['summary']),
                //  'teaser' => $this->cleanup($teaser),
                'bodytext' => $this->cleanup($item['description']),
                'media' => $this->getMediaFile($item['attach'], $item['uid']),

                'datetime' => (new DateTime($item['dtstart']))->getTimestamp(),
                'categories' => $this->getCategories($item['categories']['item'], $configuration),

                'location_simple' => nl2br($item['location']??''),
                'organizer_simple' => nl2br($item['contact']??''),

                'externalurl' => $item['url'],

                '_dynamicData' => [
                    'import_ref' => $item['uid']
                ]
            ];
            // set event end date
            if (!empty($item['dtend'])) $singleItem['event_end'] = (new DateTime($item['dtend']))->getTimestamp();
            // Update Slug
            if ($this->slugHelper) $singleItem['path_segment'] = $this->slugHelper->generate($singleItem, $configuration->getPid());
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
            // get Mine TYPE
            $mimeType = $remoteFile['@attributes']['fmtype'];
            // file content
            $file_content = base64_decode($remoteFile['b64bin']);
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
            if (is_string($feeds)) $feeds = [$feeds];

            foreach ($feeds as $cat) {
                $category = $this->findCatByName($cat);
                if ($category) {
                    $categoryIds[] = $category->getUid();
                }
            }
        }
        // return mapped IDs
        return $categoryIds;
    }
}