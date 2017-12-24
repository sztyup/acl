<?php

namespace Sztyup\Acl;

class Role extends Node
{
    protected $title;
    protected $description;

    public function __construct(string $name, $title = '', $description = '', $truth = null)
    {
        $this->title = $title;
        $this->description = $description;

        parent::__construct($name, $truth);
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

}
