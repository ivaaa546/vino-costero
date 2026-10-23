<?php

namespace App\Models\Agronomia;

use App\Models\Support\BaseModel;

class Siembra extends BaseModel
{
    protected $table = 'siembras';
    protected $primaryKey = 'id_siembra';

    protected $casts = [
        'fecha_siembra' => 'date',
        'cantidad_plantas' => 'integer',
    ];

    public function parcela()
    {
        return $this->belongsTo(Parcela::class, 'id_parcela', 'id_parcela');
    }

    public function controlesSiembra()
    {
        return $this->hasMany(ControlSiembra::class, 'id_siembra', 'id_siembra');
    }

    public function siembrasEnfermas()
    {
        return $this->hasMany(SiembraEnferma::class, 'id_siembra', 'id_siembra');
    }
}
