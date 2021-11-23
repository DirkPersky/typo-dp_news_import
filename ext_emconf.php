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


$EM_CONF[$_EXTKEY] = [
    'title' => 'News import',
    'description' => 'Import External News to EXT:news',
    'category' => 'module',
    'author' => 'Dirk Persky',
    'author_email' => 'infoy@dp-wired.de',
    'state' => 'beta',
    'clearCacheOnLoad' => true,
    'version' => '1.0.0',
    'constraints' => [
        'depends' => [
            'typo3' => '8.7.0-11.99.99',
            'news' => '7.0.0-9.9.99',
        ],
        'conflicts' => [
        ],
        'suggests' => [
        ],
    ],
];