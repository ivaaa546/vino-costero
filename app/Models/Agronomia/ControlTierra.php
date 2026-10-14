<?php

namespace App\Models\Agronomia;

use App\Models\Support\BaseModel;

class ControlTierra extends BaseModel
{
    protected $table = 'controles_tierra';
    protected $primaryKey = 'id_control_tierra';

    protected $casts = [
        'fecha_control' => 'date',
        'ph' => 'float',
        'humedad' => 'float',
        'nitrogeno' => 'float',
        'fosforo' => 'float',
        'potasio' => 'float',
    ];

    public function parcela()
    {
        return $this->belongsTo(Parcela::class, 'id_parcela', 'id_parcela');
    }
}
