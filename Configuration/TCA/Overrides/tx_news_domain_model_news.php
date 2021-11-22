<?php

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
    'import_id' => [
        'exclude' => true,
        'label' => 'LLL:EXT:dp_news_import/Resources/Private/Language/locallang.xlf:tx_news_domain_model_news.import_id',
        'config' => [
            'type' => 'text',
            'cols' => 60,
            'rows' => 20,
            'readOnly' => true,
        ]
    ]
];

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('tx_news_domain_model_news', $fields);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('tx_news_domain_model_news', 'import_data');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('tx_news_domain_model_news', 'import_id');


