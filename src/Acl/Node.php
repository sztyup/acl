<?php

namespace Sztyup\Acl;

use Illuminate\Support\Collection;
use Sztyup\Acl\Contracts\NodeRepository;
use Sztyup\Acl\Contracts\UsesAcl;
use Tree\Builder\NodeBuilderInterface;
use Tree\Node\NodeInterface;
use Tree\Node\NodeTrait;
use Tree\Visitor\YieldVisitor;

class Node implements NodeInterface
{
    use NodeTrait;

    protected $truth;
    protected $name;

    public function __construct($name, $truth)
    {
        $this->name = $name;
        $this->truth = $truth;
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * It tells wheter the current node is given to the user regardless of the persistent state
     *
     * @param UsesAcl $user
     * @return bool
     */
    public function apply(UsesAcl $user): bool
    {
        if (!is_callable($this->truth)) {
            return false;
        }

        return call_user_func($this->truth, $user);
    }

    /**
     * Converts an array represantation of permissions into a NodeTree
     *
     * @param NodeBuilderInterface $tree
     * @param $permissions array A recursive array with the permissions
     * @param NodeRepository $repository
     * @return NodeBuilderInterface The NodeInterface for the root of the tree
     */
    public static function buildTree($tree, array $permissions, NodeRepository $repository)
    {
        if ($tree == null) {
            $tree = new NodeBuilder();
            $top = true;
        } else {
            $top = false;
        }

        foreach ($permissions as $child => $properties) {
            self::buildTree(
                $tree->tree(
                    $repository->parse($child, $properties)
                ),
                $repository->getChildren($child, $properties),
                $repository
            );
        }

        if ($top) {
            return $tree;
        } else {
            return $tree->end();
        }
    }

    protected function filter(Node $root, callable $function, $inherits)
    {
        $result = new Collection();

        foreach ($root->getChildren() as $child) {
            if ($function($child)) {
                $result = $result->merge($inherits ? $child->getAncestorsAndSelf() : $child);
            }
            $result = $result->merge(
                $this->filter($child, $function, $inherits)
            );
        }

        return $result;
    }

    /**
     * Return all permission node from the tree, for which the given callback returns true
     *
     * @param callable $filterFunction
     * @param bool $inherits
     * @return array
     */
    public function filterTree(callable $filterFunction, $inherits = true)
    {
        return $this->filter($this, $filterFunction, $inherits);
    }

    /**
     * It should be overwritten to exclude dummy root
     *
     * @return array Ancestors
     */
    public function getAncestors()
    {
        $parents = [];
        $node = $this;
        while ($parent = $node->getParent()) {
            if (get_class($parent) != Node::class) {
                array_unshift($parents, $parent);
            }
            $node = $parent;
        }

        return $parents;
    }

    public function mapWithKeys(callable $function)
    {
        $collection = new Collection(
            $this->accept(new YieldVisitor())
        );

        return $collection->mapWithKeys($function);
    }
}
