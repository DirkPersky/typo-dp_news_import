<?php

namespace DirkPersky\NewsImport\EventListener;

use GeorgRinger\News\Event\NewsImportPostHydrateEvent;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class PostImportListener {

    public function postHydrate(NewsImportPostHydrateEvent $event): void
    {
        $values = $event->getImportItem();
        $news = $event->getNews();
//        $news->setLocationSimple($values['location_simple']);
//        $news->setOrganizerSimple($values['organizer_simple']);
//        if(isset($values['dtend'])){
//            $news->setEventEnd($values['dtend']);
//        } else {
//            $news->setFullDay(true);
//        }
//        $news->setIsEvent(true);
    }
}