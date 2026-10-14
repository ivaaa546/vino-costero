@extends('layouts.app')

@section('content')
<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; margin-bottom: 1.2rem;">
        <div>
            <h1 style="margin: 0; color: #5c1d2e;">{{ $title }}</h1>
            <p style="margin: 0.2rem 0 0; color: #6b6664; font-size: 0.9rem;">Módulo de administración y registro</p>
        </div>
        <a href="{{ route($resource . '.create') }}" class="btn btn-primary">+ Nuevo Registro</a>
    </div>

    <!-- Buscador -->
    <form method="GET" action="{{ route($resource . '.index') }}" class="search-bar">
        <input type="text" name="buscar" value="{{ $search ?? '' }}" class="form-control" placeholder="Buscar registros...">
        <button type="submit" class="btn btn-secondary">Buscar</button>
        @if(!empty($search))
            <a href="{{ route($resource . '.index') }}" class="btn btn-secondary" title="Limpiar filtro">✕</a>
        @endif
    </form>

    <div style="overflow-x: auto;">
        <table>
            <thead>
                <tr>
                    <th style="width: 60px;">ID</th>
                    @foreach($fields as $field)
                        @php
                            $colNames = [
                                'id_parcela' => 'Parcela',
                                'id_siembra' => 'Siembra',
                                'id_produccion' => 'Producción',
                                'id_fase' => 'Fase',
                                'nombre' => 'Nombre',
                                'ubicacion' => 'Ubicación',
                                'descripcion' => 'Descripción',
                                'estado' => 'Estado',
                                'fecha_control' => 'Fecha Control',
                                'fecha_siembra' => 'Fecha Siembra',
                                'fecha_deteccion' => 'Fecha Detección',
                                'fecha_inicio' => 'Fecha Inicio',
                                'fecha_fin' => 'Fecha Fin',
                                'fecha_cata' => 'Fecha Cata',
                                'variedad_uva' => 'Variedad Uva',
                                'cantidad_plantas' => 'Cant. Plantas',
                                'cantidad_plantas_afectadas' => 'Plantas Afectadas',
                                'estado_general' => 'Estado General',
                                'riego_realizado' => 'Riego',
                                'fertilizacion_realizada' => 'Fertilización',
                                'nombre_enfermedad' => 'Enfermedad',
                                'nivel_gravedad' => 'Gravedad',
                                'tratamiento' => 'Tratamiento',
                                'nombre_vino' => 'Nombre Vino',
                                'cantidad_uva_kg' => 'Uva (kg)',
                                'cantidad_vino_litros' => 'Vino (L)',
                                'puntuacion' => 'Puntuación',
                                'resultado' => 'Resultado',
                                'aroma' => 'Aroma',
                                'sabor' => 'Sabor',
                                'color' => 'Color',
                                'cuerpo' => 'Cuerpo',
                                'observaciones' => 'Observaciones',
                            ];
                        @endphp
                        <th>{{ $colNames[$field] ?? ucfirst(str_replace('_', ' ', $field)) }}</th>
                    @endforeach
                    <th style="text-align: right; width: 140px;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($records as $record)
                    <tr>
                        <td><strong>#{{ $record->{$key} }}</strong></td>
                        @foreach($fields as $field)
                            <td>
                                @if($field === 'id_parcela')
                                    {{ $record->parcela?->nombre ?? '#' . $record->id_parcela }}
                                @elseif($field === 'id_siembra')
                                    #{{ $record->id_siembra }} - {{ $record->siembra?->variedad_uva ?? '' }}
                                @elseif($field === 'id_produccion')
                                    {{ $record->produccion?->nombre_vino ?? '#' . $record->id_produccion }}
                                @elseif($field === 'id_fase')
                                    {{ $record->fase?->nombre ?? '#' . $record->id_fase }}
                                @elseif(in_array($field, ['riego_realizado', 'fertilizacion_realizada']))
                                    @if($record->{$field})
                                        <span class="badge badge-success">Sí</span>
                                    @else
                                        <span class="badge badge-info">No</span>
                                    @endif
                                @elseif(in_array($field, ['estado', 'estado_general', 'nivel_gravedad', 'resultado']))
                                    @php
                                        $val = (string) $record->{$field};
                                        $badgeClass = 'badge-info';
                                        if (in_array($val, ['Activa', 'Excelente', 'Bueno', 'Aprobado', 'Completada', 'Controlada'])) {
                                            $badgeClass = 'badge-success';
                                        } elseif (in_array($val, ['En crecimiento', 'En proceso', 'En tratamiento', 'Regular', 'Moderada', 'Pendiente', 'Requiere mejoras'])) {
                                            $badgeClass = 'badge-warning';
                                        } elseif (in_array($val, ['Inactiva', 'Malo', 'Grave', 'Rechazado', 'Cancelada', 'Sin recuperacion'])) {
                                            $badgeClass = 'badge-danger';
                                        }
                                    @endphp
                                    <span class="badge {{ $badgeClass }}">{{ $val }}</span>
                                @elseif(str_starts_with($field, 'fecha_'))
                                    @php
                                        $d = $record->{$field};
                                    @endphp
                                    {{ $d ? (is_string($d) ? substr($d, 0, 16) : $d->format('Y-m-d H:i')) : '-' }}
                                @elseif(in_array($field, ['cantidad_uva_kg', 'cantidad_vino_litros', 'puntuacion', 'ph', 'humedad', 'nitrogeno', 'fosforo', 'potasio']))
                                    {{ $record->{$field} !== null ? $record->{$field} : '-' }}
                                @else
                                    {{ $record->{$field} !== null && $record->{$field} !== '' ? $record->{$field} : '-' }}
                                @endif
                            </td>
                        @endforeach
                        <td style="text-align: right; white-space: nowrap;">
                            <a href="{{ route($resource . '.edit', $record->{$key}) }}" class="btn btn-secondary btn-sm">Editar</a>
                            <form action="{{ route($resource . '.destroy', $record->{$key}) }}" method="POST" style="display:inline;" onsubmit="return confirm('¿Está seguro de eliminar el registro #{{ $record->{$key} }}?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ count($fields) + 2 }}" style="text-align: center; padding: 2rem; color: #888;">
                            No se encontraron registros en este módulo.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top: 1.2rem;">
        {{ $records->links() }}
    </div>
</div>
@endsection
