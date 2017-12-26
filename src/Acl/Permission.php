<?php

namespace Sztyup\Acl;

class Permission extends Node
{
    protected $title;

    public function __construct($name, $title = '', $id = 0, $truth = null)
    {
        $this->title = $title;

        parent::__construct($name, $truth, $id);
    }

    public function getTitle(): string
    {
        return $this->title ?? '';
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
            }
        }

        return [
            new Permission($key, $title ?? '', $truth ?? null),
            $children ?? []
        ];
    }
}
