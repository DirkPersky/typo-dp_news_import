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

namespace DirkPersky\NewsImport\Aspect;

use DirkPersky\NewsImport\Domain\Model\News;

/**
 * Persist dynamic data of import
 */
class NewsImportAspect
{
    /**
     * @param array $importData
     * @param News $news
     */
    public function postHydrate(array $importData, $news)
    {
        /** @var News $news */
        if (is_array($importData['_dynamicData']) && isset($importData['_dynamicData']['import_data'])) {
            $news->setImportData($importData['_dynamicData']['import_data']);
        }
        /** @var News $news */
        if (is_array($importData['_dynamicData']) && isset($importData['_dynamicData']['import_ref'])) {
            $news->setImportRef($importData['_dynamicData']['import_ref']);
        }

    }
}