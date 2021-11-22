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

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\Container\Container;
use DirkPersky\NewsImport\Domain\Model\NewsDefault;

defined('TYPO3_MODE') or die();

$boot = function ($extKey) {
    // add Scheduler Task
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks'][\DirkPersky\NewsImport\Tasks\ImportTask::class] = [
        'extension' => $extKey,
        'title' => 'LLL:EXT:' . $extKey . '/Resources/Private/Language/locallang.xlf:task.name',
        'description' => 'LLL:EXT:' . $extKey . '/Resources/Private/Language/locallang.xlf:task.description',
        'additionalFields' => \DirkPersky\NewsImport\Tasks\ImportTaskAdditionalFieldProvider::class
    ];
    // register News Import Hool
    GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\SignalSlot\\Dispatcher')->connect(
        'GeorgRinger\\News\\Domain\\Service\\NewsImportService',
        'postHydrate',
        'DirkPersky\\NewsImport\\Aspect\\NewsImportAspect',
        'postHydrate'
    );

    $GLOBALS['TYPO3_CONF_VARS']['EXT']['news']['classes']['Domain/Model/News'][] = $extKey;

//    GeneralUtility::makeInstance(Container::class)
//        ->registerImplementation(\GeorgRinger\News\Domain\Model\NewsDefault::class, NewsDefault::class);
};

$boot('dp_news_import');
unset($boot);
