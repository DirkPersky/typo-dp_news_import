<?php
/*
 * Copyright (c) 2021.
 *
 * @category   TYPO3
 *
 * @copyright  2020 Dirk Persky (https://github.com/DirkPersky)
 * @author     Dirk Persky <dirk.persky@gmail.com>
 * @license    AGPL
 */

declare(strict_types=1);

namespace DirkPersky\NewsImport\Aspect;

use DirkPersky\NewsImport\Domain\Model\NewsDefault;

/**
 * Persist dynamic data of import
 */
class NewsImportAspect
{
    /**
     * @param array $importData
     * @param NewsDefault $news
     */
    public function postHydrate(array $importData, $news)
    {
        /** @var News $news */
        if (is_array($importData['_dynamicData']) && is_array($importData['_dynamicData']['import_data'])) {
            $news->setImportData(json_encode($importData['_dynamicData']['import_data']));
        }
        /** @var News $news */
        if (is_array($importData['_dynamicData']) && is_array($importData['_dynamicData']['import_id'])) {
            $news->setImportId(json_encode($importData['_dynamicData']['import_id']));
        }
    }
}