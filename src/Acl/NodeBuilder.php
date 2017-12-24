<?php

namespace Sztyup\Acl;

use Tree\Builder\NodeBuilder as Base;

class NodeBuilder extends Base
{
    public function nodeInstanceByValue($value = null)
    {
        if ($value == null) {
            return new Node('root', null);
        }
        return $value;
    }
}
