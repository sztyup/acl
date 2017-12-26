<?php

namespace Sztyup\Acl\Models;

use Illuminate\Database\Eloquent\Model;

class PermissionToRole extends Model
{
    public function role()
    {
        return $this->belongsTo(Role::class);
    }
}
