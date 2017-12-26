<?php

namespace Sztyup\Acl\Models;

use Illuminate\Database\Eloquent\Model;
use Sztyup\Acl\Contracts\Role as RoleContract;

class Role extends Model implements RoleContract
{
    public function user()
    {
        return $this->belongsToMany(config('acl.user_model'));
    }

    public function getName(): string
    {
        return $this->name;
    }
}
