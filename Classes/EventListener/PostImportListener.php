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

use GeorgRinger\News\Event\NewsImportPostHydrateEvent;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class PostImportListener {

    /**
     * @param NewsImportPostHydrateEvent $event
     * @return void
     */
    public function postHydrate(NewsImportPostHydrateEvent $event): void
    {
        $values = $event->getImportItem();
        $news = $event->getNews();

//        $news->setLocationSimple($values['location_simple']);
//        $news->setOrganizerSimple($values['organizer_simple']);
//        $news->setIsEvent(true);
    }
}