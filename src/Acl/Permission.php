<?php

namespace Sztyup\Acl;

class Permission extends Node
{
    protected $title;

    public function __construct($name, $title = '', $truth = null)
    {
        $this->title = $title;

        parent::__construct($name, $truth);
    }

    public function getTitle(): string
    {
        return $this->title ?? '';
    }
}
