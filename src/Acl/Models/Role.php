<?php

namespace Sztyup\Acl\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    public function user()
    {
        return $this->belongsToMany(config('acl.user_model'));
    }
}
