<?php

namespace Sztyup\Acl;

use Illuminate\Support\Collection;

class NodeCollection extends Collection
{
    protected $inheritance;

    public function setInheritance(bool $inherits)
    {
        $this->inheritance = $inherits;

        return $this;
    }

    public function isInheritanceEnabled()
    {
        return $this->inheritance;
    }

    public function withInherited()
    {
        if (!$this->inheritance) {
            return $this;
        }

        return $this->each(function (Node $node) {
            $this->items = $this->merge(
                $node->getAncestors()
            )->toArray();

            $this->items = $this->unique(function (Node $node) {
                return $node->getName();
            })->toArray();
        });
    }
}
