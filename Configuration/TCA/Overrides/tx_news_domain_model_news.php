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

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

defined('TYPO3_MODE') or die();

// Feld definieren
$fields = [
    'import_data' => [
        'exclude' => true,
        'label' => 'LLL:EXT:dp_news_import/Resources/Private/Language/locallang.xlf:tx_news_domain_model_news.import_data',
        'config' => [
            'type' => 'text',
            'cols' => 60,
            'rows' => 20,
            'readOnly' => true,
        ]
    ],
    'import_ref' => [
        'exclude' => true,
        'label' => 'LLL:EXT:dp_news_import/Resources/Private/Language/locallang.xlf:tx_news_domain_model_news.import_id',
        'config' => [
            'type' => 'text',
            'readOnly' => true,
        ]
    ]
];

ExtensionManagementUtility::addTCAcolumns('tx_news_domain_model_news', $fields);
ExtensionManagementUtility::addToAllTCAtypes('tx_news_domain_model_news', 'import_data');
ExtensionManagementUtility::addToAllTCAtypes('tx_news_domain_model_news', 'import_ref');


