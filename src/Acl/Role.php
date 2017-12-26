<?php

namespace Sztyup\Acl;

class Role extends Node
{
    protected $title;
    protected $description;
    protected $permissions;

    public function __construct(string $name, array $permissions, $title = '', $description = '', $truth = null)
    {
        $this->title = $title;
        $this->description = $description;
        $this->permissions = $permissions;

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

    public function getPermissions(): array
    {
        return $this->permissions;
    }

    protected static function parse($key, $node)
    {
        foreach ($node as $field) {
            if (is_array($field)) {
                $permissions = $field;
            } elseif (is_string($field)) {
                $title = $field;
            } elseif (is_callable($field)) {
                $truth = $field;
            }
        }

        return [
            new Role($key, $permissions ?? [], $title ?? '', $truth ?? null),
            []
        ];
    }

    public function __toString()
    {
        return $this->getName();
    }
}
