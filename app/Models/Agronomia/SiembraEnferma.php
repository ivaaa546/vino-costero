<?php

namespace App\Models\Agronomia;

use App\Models\Support\BaseModel;

class SiembraEnferma extends BaseModel
{
    protected $table = 'siembras_enfermas';
    protected $primaryKey = 'id_siembra_enferma';

    protected $casts = [
        'fecha_deteccion' => 'date',
        'cantidad_plantas_afectadas' => 'integer',
    ];

    public function siembra()
    {
        return $this->belongsTo(Siembra::class, 'id_siembra', 'id_siembra');
    }
}
