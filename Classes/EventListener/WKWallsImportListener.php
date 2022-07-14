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

namespace DirkPersky\NewsImport\EventListener;

use DirkPersky\NewsImport\Event\JSONMapperEvent;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use DateTime;

class WKWallsImportListener
{
    /**
     * @param JSONMapperEvent $event
     * @return void
     * @throws \Exception
     */
    public function parseJSON(JSONMapperEvent $event): void
    {
        $data = $event->getData();
        $items = [];
        // loop data and prepare Mapping
        foreach ($data->{'data'} as $item) {
            $id = strlen($item->{'ref_id'}) > 100 ? md5($item->{'ref_id'}) : $item->{'ref_id'};
            // import Data set
            $singleItem = [
                'import_id' => $id,

                'title' => $item->{'title'},
                'teaser' => $item->{'teaser'},
                'bodytext' => $item->{'detail'},
                'media' => $item->{'media_url'},

                'datetime' => (new DateTime($item->{'created_at'}))->getTimestamp(),
                'categories' => $item->{'feed'},

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
            // add to holder
            $items[] = $singleItem;
        }
        // set items
        $event->setItems($items);
    }
}