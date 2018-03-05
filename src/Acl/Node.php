<?php

namespace Sztyup\Acl;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Collection;
use Closure;

class Node
{
    /** @var Closure */
    protected $truth;

    /** @var string */
    protected $name;


    /** @var Node[] */
    protected $children;

    /** @var Node */
    protected $parent;

    public function __construct($name, $truth = null)
    {
        $this->name = $name;
        $this->truth = $truth;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setParent(Node $node)
    {
        $this->parent = $node;

        return $this;
    }

    public function getParent()
    {
        return $this->parent;
    }

    public function addChildren(Node $node)
    {
        $node->setParent($this);

        $this->children[] = $node;

        return $this;
    }

    public function getChildren()
    {
        return $this->children ?? [];
    }

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

    public function getAncestorsAndSelf()
    {
        return array_merge($this->getAncestors(), [$this]);
    }

    /**
     * It tells whether the current node is given to the user regardless of the persistent state
     *
     * @param Authenticatable $user
     * @return bool
     */
    public function apply(Authenticatable $user): bool
    {
        if (!is_callable($this->truth)) {
            return false;
        }

        return call_user_func($this->truth, $user);
    }

    protected function filter(Node $root, callable $function, $inherits): Collection
    {
        $result = new Collection();

        /** @var Node $child */
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
     * @param Authenticatable $user The user requesting nodes
     * @return Collection The applicable nodes
     */
    public function getNodesByDynamic(Authenticatable $user): Collection
    {
        return $this->filterTree(function (Node $node) use ($user) {
            return $node->apply($user);
        });
    }

    public function flatten(): Collection
    {
        $collection = new Collection();

        foreach ($this->getChildren() as $child) {
            $collection = $collection->merge($child->flatten());
        }

        return $collection;
    }
}
