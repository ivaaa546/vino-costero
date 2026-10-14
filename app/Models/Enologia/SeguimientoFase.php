<?php

namespace App\Models\Enologia;

use App\Models\Support\BaseModel;

class SeguimientoFase extends BaseModel
{
    protected $table = 'seguimiento_fases';
    protected $primaryKey = 'id_seguimiento';

    protected $casts = [
        'fecha_inicio' => 'datetime',
        'fecha_fin' => 'datetime',
    ];

    public function produccion()
    {
        return $this->belongsTo(Produccion::class, 'id_produccion', 'id_produccion');
    }

    public function fase()
    {
        return $this->belongsTo(FaseProduccion::class, 'id_fase', 'id_fase');
    }
}
