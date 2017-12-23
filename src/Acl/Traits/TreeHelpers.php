<?php

namespace Sztyup\Acl\Traits;

use Sztyup\Acl\Permission;
use Tree\Builder\NodeBuilder;
use Tree\Builder\NodeBuilderInterface;
use Tree\Node\NodeInterface;

trait TreeHelpers
{
    protected function parsePermission(array $properties)
    {
        foreach ($properties as $field) {
            if (is_array($field)) {
                $children = $field;
            } elseif (is_string($field)) {
                $title = $field;
            } elseif (is_callable($field)) {
                $truth = $field;
            }
        }

        return [
            $title ?? '',
            $truth ?? null,
            $children ?? []
        ];
    }

    /**
     * Converts an array represantation of permissions into a NodeTree
     *
     * @param NodeBuilderInterface $tree
     * @param $permissions array A recursive array with the permissions
     * @return NodeInterface The NodeInterface for the root of the tree
     */
    protected function addPermissionsToTree(NodeBuilderInterface $tree, array $permissions)
    {
        foreach ($permissions as $child => $properties) {
            list($title, $truth, $children) = $this->parsePermission($properties);

            $permission = new Permission($child, $title, $truth);

            if (count($children) > 0) {
                $this->addPermissionsToTree(
                    $tree->tree($permission),
                    $children
                );
            } else {
                $tree->leaf($permission);
            }
        }

        return $tree->end()->getNode();
    }

    protected function filter(NodeInterface $root, callable $function, $inherits)
    {
        $result = [];

        foreach ($root->getChildren() as $child) {
            if ($function($child->getValue())) {
                $result[] = $inherits ? $child->getAncestorsAndSelf(): $child;
            }
            $result = array_merge($result, $this->filter($child, $function, $inherits));
        }

        return $result;
    }

    /**
     * Return all permission node from the tree, for which the given callback returns true
     * @param NodeInterface $tree The tree to traverse
     * @param callable $filterFunction
     * @param bool $inherits
     * @return array
     */
    protected function filterTree(NodeInterface $tree, callable $filterFunction, $inherits = true)
    {
        $result = $this->filter($tree, $filterFunction, $inherits);

        // Extract values from nodes
        return array_map(
            function (NodeInterface $node) {
                return $node->getValue();
            },
            $result
        );
    }
}
