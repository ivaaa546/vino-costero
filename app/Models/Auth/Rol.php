<?php

namespace App\Models\Auth;

use App\Models\Support\BaseModel;
use App\Models\Auth\Usuario;

class Rol extends BaseModel
{
    protected $table = 'roles';
    protected $primaryKey = 'id_rol';

    public function usuarios()
    {
        return $this->hasMany(Usuario::class, 'id_rol', 'id_rol');
    }
}
