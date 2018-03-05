<?php

namespace Sztyup\Acl;

class Permission extends Node
{
    protected $title;
    protected $sensitive;

    public function __construct($name, $title = '', $truth = null, $sensitive = false)
    {
        $this->title = $title;
        $this->sensitive = $sensitive;

        parent::__construct($name, $truth);
    }

    public function getTitle(): string
    {
        return $this->title ?? '';
    }

    public function isSensitive(): bool
    {
        return $this->sensitive ?? false;
    }

    public function __toString()
    {
        return $this->getName();
    }
}
