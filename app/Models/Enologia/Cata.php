<?php

namespace App\Models\Enologia;

use App\Models\Support\BaseModel;

class Cata extends BaseModel
{
    protected $table = 'catas';
    protected $primaryKey = 'id_cata';

    protected $casts = [
        'fecha_cata' => 'date',
        'puntuacion' => 'float',
    ];

    public function produccion()
    {
        return $this->belongsTo(Produccion::class, 'id_produccion', 'id_produccion');
    }
}
