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
use DirkPersky\NewsImport\Event\JSONMapperEvent;
use Exception;

class JsonMapper extends AbstractMapper implements MapperInterface
{
    /**
     * @param TaskConfiguration $configuration
     * @return array|mixed
     * @throws Exception
     */
    public function execute(TaskConfiguration $configuration)
    {
        $data = file_get_contents($configuration->getPath());
        if (empty($data)) {
            throw new Exception('Empty Response from Source');
        }
        // encode
        $data = json_decode($data);
        if (empty($data->{'data'})) {
            throw new Exception('Empty Data-Object from Source');
        }
        // call event for formating
        $event = $this->eventDispatcher->dispatch(
            new JSONMapperEvent($data, $configuration)
        );
        // Item holder
        $items = $event->getItems();
        // return news set
        return $this->prePareItems($items, $configuration);
    }
}