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

namespace DirkPersky\NewsImport\Domain\Model;

class News extends \GeorgRinger\News\Domain\Model\News
{
    /**
     * @var string|null
     */
    protected $importData;

    /**
     * @var string|null
     */
    protected $importRef;

    /**
     * @return string|null
     */
    public function getImportData(): ?string
    {
        return $this->importData;
    }

    /**
     * @param string|null $importData
     */
    public function setImportData(?string $importData): void
    {
        $this->importData = $importData;
    }

    /**
     * @return string|null
     */
    public function getImportRef(): ?string
    {
        return $this->importRef;
    }

    /**
     * @param string|null $importRef
     */
    public function setImportRef(?string $importRef): void
    {
        $this->importRef = $importRef;
    }



}