<?php

namespace Sztyup\Acl\Contracts;

interface Role
{
    public function getName(): string;
    public function getTitle(): string;
    public function getDescription(): string;
    public function getSensitive(): string;
}
