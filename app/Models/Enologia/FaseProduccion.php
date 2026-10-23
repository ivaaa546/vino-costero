<?php

namespace App\Models\Enologia;

use App\Models\Support\BaseModel;

class FaseProduccion extends BaseModel
{
    protected $table = 'fases_produccion';
    protected $primaryKey = 'id_fase';

    protected $casts = [
        'orden_fase' => 'integer',
    ];

    public function seguimientos()
    {
        return $this->hasMany(SeguimientoFase::class, 'id_fase', 'id_fase');
    }
}
