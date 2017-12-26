<?php

namespace Sztyup\Acl;

use Illuminate\Support\Collection;
use Tree\Node\NodeInterface;
use Tree\Visitor\PreOrderVisitor;

class TreeVisitor extends PreOrderVisitor
{
    public function visit(NodeInterface $node)
    {
        // Needed to exclude dummy root from visiting
        if (get_class($node) != Node::class) {
            return parent::visit($node);
        }

        $nodes = new Collection();

        foreach ($node->getChildren() as $child) {
            $nodes = $nodes->merge(parent::visit($child));
        }

        return $nodes;
    }
}
