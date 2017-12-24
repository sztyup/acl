<?php

namespace Sztyup\Acl\Contracts;

interface NodeRepository
{
    public function parse($key, $node): array;

    public function getChildren($key, $node): array;
}
