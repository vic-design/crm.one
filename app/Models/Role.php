<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    // Добавляем поля для работы с правами, если нужно
    protected $fillable = [
        'name',
        'guard_name',
    ];
}
