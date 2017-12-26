<?php

namespace Sztyup\Acl;

use Illuminate\Support\Collection;
use Sztyup\Acl\Contracts\HasAcl;
use Tree\Builder\NodeBuilderInterface;
use Tree\Node\NodeInterface;
use Tree\Node\NodeTrait;

class Node implements NodeInterface
{
    use NodeTrait;

    protected $id;
    protected $truth;
    protected $name;

    public function __construct($name, $truth, $id = 0)
    {
        $this->id = $id;
        $this->name = $name;
        $this->truth = $truth;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * It tells whether the current node is given to the user regardless of the persistent state
     *
     * @param HasAcl $user
     * @return bool
     */
    public function apply(HasAcl $user): bool
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
     * @return NodeBuilderInterface The NodeInterface for the root of the tree
     */
    public static function buildTree($tree, array $permissions)
    {
        if ($tree == null) {
            $tree = new NodeBuilder();
            $top = true;
        } else {
            $top = false;
        }

        foreach ($permissions as $child => $properties) {
            list($permission, $children) = static::parse($child, $properties);

            self::buildTree(
                $tree->tree($permission),
                $children
            );
        }

        if ($top) {
            return $tree;
        } else {
            return $tree->end();
        }
    }

    protected static function parse($key, $node)
    {
        return [
            new Node('dummy', null),
            []
        ];
    }

    protected function filter(Node $root, callable $function, $inherits): Collection
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
     * @return Collection
     */
    public function filterTree(callable $filterFunction, $inherits = true): Collection
    {
        return $this->filter($this, $filterFunction, $inherits);
    }

    /**
     * It is overwritten to exclude dummy root
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
        $collection = $this->flatten();

        return $collection->mapWithKeys($function);
    }

    /**
     * Returns all node (and theyre accendants if inheritance is enabled) who are listed in the values array
     *
     * @param array $values The nodes matched
     * @return Collection
     */
    public function getNodesByNames(array $values): Collection
    {
        return $this->filterTree(function (Node $node) use ($values) {
            return in_array($node->getName(), $values);
        });
    }

    /**
     * Gives back all nodes applicable to the given user
     *
     * @param HasAcl $user The user requesting nodes
     * @return Collection The applicable nodes
     */
    public function getNodesByDynamic(HasAcl $user): Collection
    {
        return $this->filterTree(function (Node $node) use ($user) {
            return $node->apply($user);
        });
    }

    public function flatten(): Collection
    {
        return $this->accept(new TreeVisitor());
    }
}
