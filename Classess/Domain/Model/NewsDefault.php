<?php

namespace DirkPersky\NewsImport\Domain\Model;

class NewsDefault extends \GeorgRinger\News\Domain\Model\NewsDefault
{
    /**
     * @var string|null
     */
    protected $importData;

    /**
     * @var string|null
     */
    protected $importId;

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
    public function getImportId(): ?string
    {
        return $this->importId;
    }

    /**
     * @param string|null $importId
     */
    public function setImportId(?string $importId): void
    {
        $this->importId = $importId;
    }

}