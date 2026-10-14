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
    }
}
