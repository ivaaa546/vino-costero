<?php

namespace App\Models\Agronomia;

use App\Models\Support\BaseModel;

class Parcela extends BaseModel
{
    protected $table = 'parcelas';
    protected $primaryKey = 'id_parcela';

    public function controlesTierra()
    {
        return $this->hasMany(ControlTierra::class, 'id_parcela', 'id_parcela');
    }

    public function siembras()
    {
        return $this->hasMany(Siembra::class, 'id_parcela', 'id_parcela');
    }

    public function producciones()
    {
        return $this->hasMany(Produccion::class, 'id_parcela', 'id_parcela');
    }
}
