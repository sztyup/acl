<?php

namespace Sztyup\Acl;

use Closure;
use Illuminate\Contracts\Auth\Authenticatable;

class Permission
{
    protected $name;
    protected $title;
    protected $truth;

    public function __construct($name, $title, $truth)
    {
        $this->name = $name;
        $this->title = $title;
        if (is_callable($truth)) {
            $this->truth = $truth;
        }
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getTitle(): string
    {
        return $this->title ?? '';
    }

    /**
     * It tells wheter the current permissions is given to the user regardless of roles
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
}
