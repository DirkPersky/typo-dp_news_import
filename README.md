# TYPO3 CMS Extension "dp_news_import"

This extension provides a json import schedule for `EXT:News`


## Example Listener
```php
namespace DirkPersky\Theme\EventListener;

use DirkPersky\NewsImport\Event\XMLMapperEvent;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use DateTime;

class PrepareImportListener {

    public function parseXML(XMLMapperEvent $event): void
    {
        $array = $event->getData();
        $items = [];
        // loop Events
        foreach ($array['iCalendar']['vcalendar']['vevent'] as $item) {
            if(!in_array($item['class'],  ['PUBLIC'])) continue;
            // import Data set
            $singleItem = [
                'import_id' => $item['uid'],
                'import_source' => 'dp_news_import',

                'is_event' => 1,

                'title' => $item['summary'],
                //  'teaser' => $mapper->cleanup($teaser),
                'bodytext' => $item['description'],
                'media' => $this->media($item['attach']),

                'datetime' => (new DateTime($item['dtstart']))->getTimestamp(),
                'categories' => $item['categories']['item'],

                'location_simple' => nl2br($item['location']??''),
                'organizer_simple' => nl2br($item['contact']??''),

                'externalurl' => $item['url'],
                'full_day' => false,
            ];
            // set event end date
            if (!empty($item['dtend'])) {
                $end = new DateTime($item['dtend']);
                $start = new DateTime($item['dtstart']);
                // set end Date
                $singleItem['event_end'] = $end;
                // is More than 1 day diff mark as full day
                if($start->diff($end)->days > 0) $singleItem['full_day'] = true; // set as Fullday
            } else $singleItem['full_day'] = true; // set as Fullday
            // add to holder
            $items[] = $singleItem;
        }
        // set items
        $event->setItems($items);
    }

    private function media($remoteFile)
    {
        if(!$remoteFile) return;

        return [
            'mimeType' => $remoteFile['@attributes']['fmtype'],
            'content' => base64_decode($remoteFile['b64bin']),
        ];
    }
}
```

## Register Listener in Services.yaml
```yaml
services:
  DirkPersky\Theme\EventListener\PrepareImportListener:
    tags:
      - name: event.listener
        identifier: 'dp-theme-import-xml'
        method: 'parseXML'
        event: DirkPersky\NewsImport\Event\XMLMapperEvent

```