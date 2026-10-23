<?php

namespace Database\Seeders;

use App\Models\Auth\Rol;
use App\Models\Auth\Usuario;
use App\Models\Enologia\FaseProduccion;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['id_rol' => 1, 'nombre' => 'Administrador', 'descripcion' => 'Acceso administrativo al sistema'],
            ['id_rol' => 2, 'nombre' => 'Enologo', 'descripcion' => 'Control de producción y evaluación de vinos'],
            ['id_rol' => 3, 'nombre' => 'Ingeniero Agronomo', 'descripcion' => 'Control de parcelas, tierra y siembras'],
            ['id_rol' => 4, 'nombre' => 'Operador', 'descripcion' => 'Registro de actividades operativas'],
        ];

        foreach ($roles as $role) {
            Rol::updateOrCreate(['nombre' => $role['nombre']], $role + ['estado' => 'Activo']);
        }

        // Usuario administrador inicial (Javier / 12345678)
        Usuario::updateOrCreate(
            ['correo' => 'jacs@gmail.com'],
            [
                'id_rol' => 1,
                'nombre' => 'Javier',
                'password_hash' => '$2y$10$U2B5tYvEeVmjTWPUs42HleK2xA4K/aCESc4aM.NBP/dNLcX5OX86q',
                'estado' => 'Activo',
            ]
        );

        // Usuarios adicionales para pruebas de roles
        Usuario::updateOrCreate(
            ['correo' => 'enologo@vinocostero.com'],
            [
                'id_rol' => 2,
                'nombre' => 'Carlos Enólogo',
                'password_hash' => Hash::make('12345678'),
                'estado' => 'Activo',
            ]
        );

        Usuario::updateOrCreate(
            ['correo' => 'agronomo@vinocostero.com'],
            [
                'id_rol' => 3,
                'nombre' => 'Maria Agrónoma',
                'password_hash' => Hash::make('12345678'),
                'estado' => 'Activo',
            ]
        );

        Usuario::updateOrCreate(
            ['correo' => 'operador@vinocostero.com'],
            [
                'id_rol' => 4,
                'nombre' => 'Luis Operador',
                'password_hash' => Hash::make('12345678'),
                'estado' => 'Activo',
            ]
        );

        $fases = [
            ['orden_fase' => 1, 'nombre' => 'Recepción de uvas', 'descripcion' => 'Recepción y revisión de la materia prima'],
            ['orden_fase' => 2, 'nombre' => 'Prensado', 'descripcion' => 'Extracción del mosto de las uvas'],
            ['orden_fase' => 3, 'nombre' => 'Fermentación', 'descripcion' => 'Transformación del mosto en vino'],
            ['orden_fase' => 4, 'nombre' => 'Clarificación', 'descripcion' => 'Separación de partículas e impurezas'],
            ['orden_fase' => 5, 'nombre' => 'Estabilización', 'descripcion' => 'Preparación y estabilización del vino'],
            ['orden_fase' => 6, 'nombre' => 'Filtrado', 'descripcion' => 'Filtración previa al envasado'],
            ['orden_fase' => 7, 'nombre' => 'Control de calidad', 'descripcion' => 'Evaluación final de la producción'],
        ];

        foreach ($fases as $fase) {
            FaseProduccion::updateOrCreate(
                ['orden_fase' => $fase['orden_fase']],
                $fase + ['estado' => 'Activa']
            );
        }

        // 1. Parcelas
        $p1 = \App\Models\Agronomia\Parcela::updateOrCreate(
            ['nombre' => 'Parcela Los Acantilados Sur'],
            [
                'ubicacion' => 'Sector Ladera Sur, Lote B-12',
                'descripcion' => 'Terreno con suave pendiente y suelo franco-arenoso. Excelente exposición solar y brisa marina constante.',
                'estado' => 'Activa',
            ]
        );

        $p2 = \App\Models\Agronomia\Parcela::updateOrCreate(
            ['nombre' => 'Parcela Brisas del Mar'],
            [
                'ubicacion' => 'Zona Norte, Lote A-03 (800m de costa)',
                'descripcion' => 'Suelo arcilloso-calcáreo con influencia directa de neblina matinal. Ideal para maduración lenta de uvas blancas.',
                'estado' => 'Activa',
            ]
        );

        $p3 = \App\Models\Agronomia\Parcela::updateOrCreate(
            ['nombre' => 'Parcela Mirador del Valle'],
            [
                'ubicacion' => 'Terraza Alta, Sector Cordillera de la Costa',
                'descripcion' => 'Suelo pedregoso y granítico. Gran amplitud térmica entre día y noche.',
                'estado' => 'Activa',
            ]
        );

        // 2. Controles de Tierra
        \App\Models\Agronomia\ControlTierra::updateOrCreate(
            ['id_parcela' => $p1->id_parcela, 'fecha_control' => '2026-03-10'],
            [
                'ph' => 6.4,
                'humedad' => 45.5,
                'nitrogeno' => 32.0,
                'fosforo' => 18.5,
                'potasio' => 195.0,
                'observaciones' => 'Nutrientes en rango óptimo. Suelo con excelente estructura y porosidad.',
            ]
        );

        \App\Models\Agronomia\ControlTierra::updateOrCreate(
            ['id_parcela' => $p2->id_parcela, 'fecha_control' => '2026-03-12'],
            [
                'ph' => 6.8,
                'humedad' => 52.0,
                'nitrogeno' => 28.0,
                'fosforo' => 15.0,
                'potasio' => 170.0,
                'observaciones' => 'Ligera retención de humedad por neblina costera. Aporte equilibrado de minerales.',
            ]
        );

        // 3. Siembras
        $s1 = \App\Models\Agronomia\Siembra::updateOrCreate(
            ['id_parcela' => $p1->id_parcela, 'variedad_uva' => 'Cabernet Sauvignon'],
            [
                'fecha_siembra' => '2024-08-15',
                'cantidad_plantas' => 2800,
                'estado' => 'En producción',
                'observaciones' => 'Portainjerto 110R con gran vigor vegetativo y resistencia al estrés hídrico.',
            ]
        );

        $s2 = \App\Models\Agronomia\Siembra::updateOrCreate(
            ['id_parcela' => $p2->id_parcela, 'variedad_uva' => 'Sauvignon Blanc'],
            [
                'fecha_siembra' => '2024-09-01',
                'cantidad_plantas' => 3200,
                'estado' => 'En producción',
                'observaciones' => 'Conducción en espaldera simple con orientación norte-sur para captación solar.',
            ]
        );

        $s3 = \App\Models\Agronomia\Siembra::updateOrCreate(
            ['id_parcela' => $p3->id_parcela, 'variedad_uva' => 'Pinot Noir'],
            [
                'fecha_siembra' => '2025-07-20',
                'cantidad_plantas' => 2100,
                'estado' => 'En crecimiento',
                'observaciones' => 'Plantación joven en fase de formación de canopia.',
            ]
        );

        // 4. Controles de Siembra
        \App\Models\Agronomia\ControlSiembra::updateOrCreate(
            ['id_siembra' => $s1->id_siembra, 'fecha_control' => '2026-04-05'],
            [
                'estado_general' => 'Excelente',
                'riego_realizado' => true,
                'fertilizacion_realizada' => true,
                'observaciones' => 'Brotación vigorosa y uniforme. Riego por goteo aplicado durante 4 horas.',
            ]
        );

        \App\Models\Agronomia\ControlSiembra::updateOrCreate(
            ['id_siembra' => $s2->id_siembra, 'fecha_control' => '2026-04-08'],
            [
                'estado_general' => 'Bueno',
                'riego_realizado' => true,
                'fertilizacion_realizada' => false,
                'observaciones' => 'Hojas limpias y racimos en cuaje normal. Sin necesidad de fertilizante adicional.',
            ]
        );

        // 5. Siembras Enfermas
        \App\Models\Agronomia\SiembraEnferma::updateOrCreate(
            ['id_siembra' => $s1->id_siembra, 'nombre_enfermedad' => 'Oídio (Uncinula necator)'],
            [
                'fecha_deteccion' => '2026-04-12',
                'cantidad_plantas_afectadas' => 15,
                'nivel_gravedad' => 'Leve',
                'tratamiento' => 'Aplicación preventiva de azufre micronizado en polvo en el sector noreste de la espaldera.',
                'estado' => 'Controlada',
                'observaciones' => 'Foco detectado a tiempo en hojas basales. Ya no se observan manchas blanquecinas activas.',
            ]
        );

        // 6. Producciones
        $prod1 = \App\Models\Enologia\Produccion::updateOrCreate(
            ['nombre_vino' => 'Vino Costero Reserva Cabernet Sauvignon 2025'],
            [
                'id_parcela' => $p1->id_parcela,
                'variedad_uva' => 'Cabernet Sauvignon',
                'fecha_inicio' => '2025-03-20',
                'fecha_fin' => '2025-11-30',
                'cantidad_uva_kg' => 4500.00,
                'cantidad_vino_litros' => 3150.00,
                'estado' => 'Finalizada',
                'observaciones' => 'Cosecha manual matutina. Gran concentración polifenólica y madurez fenólica impecable.',
            ]
        );

        $prod2 = \App\Models\Enologia\Produccion::updateOrCreate(
            ['nombre_vino' => 'Brisa Costera Sauvignon Blanc Joven 2026'],
            [
                'id_parcela' => $p2->id_parcela,
                'variedad_uva' => 'Sauvignon Blanc',
                'fecha_inicio' => '2026-02-15',
                'fecha_fin' => null,
                'cantidad_uva_kg' => 3800.00,
                'cantidad_vino_litros' => 2600.00,
                'estado' => 'En proceso',
                'observaciones' => 'Fermentación en cubas de acero inoxidable a temperatura controlada (14°C - 16°C).',
            ]
        );

        // 7. Seguimiento de Fases
        $faseRecepcion = FaseProduccion::where('orden_fase', 1)->first();
        $fasePrensado = FaseProduccion::where('orden_fase', 2)->first();
        $faseFermentacion = FaseProduccion::where('orden_fase', 3)->first();
        $faseCalidad = FaseProduccion::where('orden_fase', 7)->first();

        if ($faseRecepcion) {
            \App\Models\Enologia\SeguimientoFase::updateOrCreate(
                ['id_produccion' => $prod1->id_produccion, 'id_fase' => $faseRecepcion->id_fase],
                [
                    'fecha_inicio' => '2025-03-20',
                    'fecha_fin' => '2025-03-20',
                    'estado' => 'Completada',
                    'observaciones' => 'Recepción de 4,500 kg en cajas de 15 kg. Despalillado inmediato.',
                ]
            );
        }

        if ($faseFermentacion) {
            \App\Models\Enologia\SeguimientoFase::updateOrCreate(
                ['id_produccion' => $prod1->id_produccion, 'id_fase' => $faseFermentacion->id_fase],
                [
                    'fecha_inicio' => '2025-03-22',
                    'fecha_fin' => '2025-04-10',
                    'estado' => 'Completada',
                    'observaciones' => 'Fermentación alcohólica completada en 19 días con levaduras seleccionadas.',
                ]
            );

            \App\Models\Enologia\SeguimientoFase::updateOrCreate(
                ['id_produccion' => $prod2->id_produccion, 'id_fase' => $faseFermentacion->id_fase],
                [
                    'fecha_inicio' => '2026-02-18',
                    'fecha_fin' => null,
                    'estado' => 'En proceso',
                    'observaciones' => 'Densidad actual 1020 g/l. Control de temperatura constante para preservar aromas primarios.',
                ]
            );
        }

        if ($faseCalidad) {
            \App\Models\Enologia\SeguimientoFase::updateOrCreate(
                ['id_produccion' => $prod1->id_produccion, 'id_fase' => $faseCalidad->id_fase],
                [
                    'fecha_inicio' => '2025-11-20',
                    'fecha_fin' => '2025-11-28',
                    'estado' => 'Completada',
                    'observaciones' => 'Análisis enológico de laboratorio conforme: acidez total 5.2 g/l, alcohol 13.8% vol.',
                ]
            );
        }

        // 8. Catas
        \App\Models\Enologia\Cata::updateOrCreate(
            ['id_produccion' => $prod1->id_produccion, 'fecha_cata' => '2025-12-05'],
            [
                'aroma' => 'Frutos negros maduros (moras y casis), con notas especiadas, vainilla sutil y toque mineral marino.',
                'sabor' => 'Entrada suave, taninos sedosos y maduros. Excelente balance de acidez y largo final en boca.',
                'color' => 'Rojo rubí profundo y brillante con ribetes granate.',
                'cuerpo' => 'Cuerpo medio-alto, elegante y estructurado.',
                'puntuacion' => 93.50,
                'resultado' => 'Aprobado',
                'observaciones' => 'Vino con gran potencial de guarda (3-5 años). Aprobado unánimemente para embotellado y comercialización.',
            ]
        );
    }
}
