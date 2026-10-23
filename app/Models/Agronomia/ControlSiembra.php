<?php

namespace App\Models\Agronomia;

use App\Models\Support\BaseModel;

class ControlSiembra extends BaseModel
{
    protected $table = 'controles_siembra';
    protected $primaryKey = 'id_control_siembra';

    protected $casts = [
        'fecha_control' => 'date',
        'riego_realizado' => 'boolean',
        'fertilizacion_realizada' => 'boolean',
    ];

    public function siembra()
    {
        return $this->belongsTo(Siembra::class, 'id_siembra', 'id_siembra');
    }
}
