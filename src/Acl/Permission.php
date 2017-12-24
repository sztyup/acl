<?php

namespace Sztyup\Acl;

class Permission extends Node
{
    protected $title;

    public function __construct($name, $title, $truth)
    {
        $this->title = $title;

        if (config('acl.dynamic_roles') && !is_callable($truth)) {
            throw new \InvalidArgumentException('truth parameter is not callable, but it should be');
        }

        parent::__construct($name, $truth);
    }

    public function getTitle(): string
    {
        return $this->title ?? '';
    }
}
