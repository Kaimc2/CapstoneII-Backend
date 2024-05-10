<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    use HasFactory;
    protected $table = 'permissions';
    public function getPermission()
    {
        return $this->belongsToMany(Permission::class, 'role_has_permissions');
    }

}
