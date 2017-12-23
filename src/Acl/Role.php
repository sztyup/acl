<?php

namespace Sztyup\Acl;

class Role
{
    protected $name;
    protected $title;
    protected $truth;

    public function __construct($name, $title)
    {
        $this->name = $name;
        $this->title = $title;
    }

    public function getTitle(): string
    {
        return $this->title ?? '';
    }

    public function getName(): string
    {
        return $this->name;
    }
}
