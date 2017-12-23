<?php

namespace Sztyup\Acl;

class Role
{
    protected $id;
    protected $name;
    protected $title;
    protected $truth;

    public function __construct(int $id, string $name, $title)
    {
        $this->id = $id;
        $this->name = $name;
        $this->title = $title;
    }

    public function getId(): int
    {
        return $this->id;
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
