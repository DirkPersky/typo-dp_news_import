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

namespace DirkPersky\NewsImport\Model;

class TaskConfiguration
{
    /** @var string|null */
    protected $format;

    /** @var string */
    protected $path;

    /** @var int */
    protected $pid;

    /** @var string|null */
    protected $mapping;

    /** @var bool */
    protected $setSlug = false;

    /**
     * @return string|null
     */
    public function getFormat(): ?string
    {
        return $this->format;
    }

    /**
     * @param string|null $format
     */
    public function setFormat(?string $format): void
    {
        $this->format = $format;
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @param string $path
     */
    public function setPath(string $path): void
    {
        $this->path = $path;
    }

    /**
     * @return int
     */
    public function getPid(): int
    {
        return $this->pid;
    }

    /**
     * @param int $pid
     */
    public function setPid(int $pid): void
    {
        $this->pid = $pid;
    }

    /**
     * @return string|null
     */
    public function getMapping(): ?string
    {
        return $this->mapping;
    }

    /**
     * @param string|null $mapping
     */
    public function setMapping(?string $mapping): void
    {
        $this->mapping = $mapping;
    }

    /**
     * @return bool
     */
    public function isSetSlug(): bool
    {
        return $this->setSlug;
    }

    /**
     * @param bool $setSlug
     */
    public function setSetSlug(bool $setSlug): void
    {
        $this->setSlug = $setSlug;
    }
}