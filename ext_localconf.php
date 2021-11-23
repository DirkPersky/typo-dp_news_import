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

defined('TYPO3_MODE') or die();

$boot = function ($extKey) {
    // New TYPO3 Version
    if (class_exists(AbstractAdditionalFieldProvider::class) === true) {
        $additionalFields = \DirkPersky\NewsImport\Tasks\ImportTaskAdditionalFieldProvider::class;
    } else {
        // TYPO3 8.7
        $additionalFields = \DirkPersky\NewsImport\Tasks\ImportTaskAdditionalFieldProviderOld::class;
    }
    // add Scheduler Task
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks'][\DirkPersky\NewsImport\Tasks\ImportTask::class] = [
        'extension' => $extKey,
        'title' => 'LLL:EXT:' . $extKey . '/Resources/Private/Language/locallang.xlf:task.name',
        'description' => 'LLL:EXT:' . $extKey . '/Resources/Private/Language/locallang.xlf:task.description',
        'additionalFields' => $additionalFields
    ];
    // register News Import Hool
    \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\SignalSlot\Dispatcher::class)->connect(
        \GeorgRinger\News\Domain\Service\NewsImportService::class,
        'postHydrate',
        \DirkPersky\NewsImport\Aspect\NewsImportAspect::class,
        'postHydrate'
    );

    $GLOBALS['TYPO3_CONF_VARS']['EXT']['news']['classes']['Domain/Model/News'][] = $extKey;

//    \TYPO3\CMS\Extbase\Object\Container\Container::makeInstance(Container::class)
//        ->registerImplementation(\GeorgRinger\News\Domain\Model\NewsDefault::class, \DirkPersky\NewsImport\Domain\Model\NewsDefault::class);
};

$boot('dp_news_import');
unset($boot);
