<?php

namespace Sztyup\Acl;

use Tree\Builder\NodeBuilder as Base;

class NodeBuilder extends Base
{
    public function nodeInstanceByValue($value = null)
    {
        return $value;
    }
}
