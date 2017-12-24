<?php

namespace Sztyup\Acl\Contracts;

use Sztyup\Acl\Node;

interface NodeRepository
{
    public function parse($key, $node): Node;

    public function getChildren($key, $node): array;
}
