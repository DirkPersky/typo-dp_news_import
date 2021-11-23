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

namespace DirkPersky\NewsImport\Mapper;

use DirkPersky\NewsImport\Model\TaskConfiguration;

interface MapperInterface {
    /**
     * @param TaskConfiguration $configuration
     * @return array
     */
    public function execute(TaskConfiguration $configuration);
}