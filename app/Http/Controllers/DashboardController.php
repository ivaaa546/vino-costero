<?php

namespace App\Http\Controllers;

use App\Models\Agronomia\ControlSiembra;
use App\Models\Agronomia\ControlTierra;
use App\Models\Agronomia\Parcela;
use App\Models\Agronomia\Siembra;
use App\Models\Agronomia\SiembraEnferma;
use App\Models\Enologia\Cata;
use App\Models\Enologia\Produccion;
use App\Models\Enologia\SeguimientoFase;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function __invoke()
    {
        $user = Auth::user();

        $counts = [
            'Parcelas' => Parcela::count(),
            'Controles de Tierra' => ControlTierra::count(),
            'Siembras' => Siembra::count(),
            'Controles de Siembra' => ControlSiembra::count(),
            'Siembras Enfermas' => SiembraEnferma::count(),
            'Producciones' => Produccion::count(),
            'Seguimiento de Fases' => SeguimientoFase::count(),
            'Catas' => Cata::count(),
        ];

        return view('dashboard', compact('counts', 'user'));
    }
}
