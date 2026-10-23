<?php

namespace App\Models\Enologia;

use App\Models\Agronomia\Parcela;
use App\Models\Support\BaseModel;

class Produccion extends BaseModel
{
    protected $table = 'producciones';
    protected $primaryKey = 'id_produccion';

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
        'cantidad_uva_kg' => 'float',
        'cantidad_vino_litros' => 'float',
    ];

    public function parcela()
    {
        return $this->belongsTo(Parcela::class, 'id_parcela', 'id_parcela');
    }

    public function seguimientos()
    {
        return $this->hasMany(SeguimientoFase::class, 'id_produccion', 'id_produccion');
    }

    public function catas()
    {
        return $this->hasMany(Cata::class, 'id_produccion', 'id_produccion');
    }
}
