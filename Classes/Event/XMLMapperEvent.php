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

namespace DirkPersky\NewsImport\Event;

final class XMLMapperEvent {
    private $data;
    private $config;
    private $items = [];

    /**
     * @param $data
     * @param $config
     */
    public function __construct($data, $config)
    {
        $this->data = $data;
        $this->config = $config;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @return mixed
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @param array $items
     */
    public function setItems(array $items): void
    {
        $this->items = array_merge($this->items, $items);
    }

    /**
     * @return array
     */
    public function getItems(): array
    {
        return $this->items;
    }

}