<?php

namespace Sztyup\Acl;

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

        $nodes = [];

        foreach ($node->getChildren() as $child) {
            $nodes = array_merge($nodes, parent::visit($child));
        }

        return $nodes;
    }
}
