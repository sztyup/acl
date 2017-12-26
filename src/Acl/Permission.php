<?php

namespace Sztyup\Acl;

class Permission extends Node
{
    protected $title;
    protected $sensitive;

    public function __construct($name, $title = '', $id = 0, $truth = null, $sensitive = false)
    {
        $this->title = $title;
        $this->sensitive = $sensitive;

        parent::__construct($name, $truth, $id);
    }

    public function getTitle(): string
    {
        return $this->title ?? '';
    }

    public function isSensitive(): bool
    {
        return $this->sensitive ?? false;
    }

    protected static function parse($key, $node)
    {
        foreach ($node as $field) {
            if (is_array($field)) {
                $children = $field;
            } elseif (is_string($field)) {
                $title = $field;
            } elseif (is_callable($field)) {
                $truth = $field;
            } elseif (is_bool($field)) {
                $sensitive = $field;
            }
        }

        return [
            new Permission($key, $title ?? '', $truth ?? null, $sensitive ?? false),
            $children ?? []
        ];
    }

    public function __toString()
    {
        return $this->getName();
    }
}
