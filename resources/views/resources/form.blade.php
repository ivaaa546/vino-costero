@extends('layouts.app')

@section('content')
<div class="card" style="max-width: 800px; margin: 0 auto;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; border-bottom: 1px solid #eee; padding-bottom: 0.8rem;">
        <h1 style="margin: 0; color: #5c1d2e; font-size: 1.5rem;">
            {{ $record ? 'Editar Registro #' . $record->{$key} : 'Nuevo Registro' }} - {{ $title }}
        </h1>
        <a href="{{ route($resource . '.index') }}" class="btn btn-secondary btn-sm">← Volver al listado</a>
    </div>

    <form method="POST" action="{{ $record ? route($resource . '.update', $record->{$key}) : route($resource . '.store') }}">
        @csrf
        @if($record)
            @method('PUT')
        @endif

        <div class="grid-2">
            @foreach($fields as $field)
                @php
                    $val = old($field, $record?->{$field});
                    $label = ucfirst(str_replace('_', ' ', $field));
                    $isFullWidth = in_array($field, ['descripcion', 'observaciones', 'tratamiento', 'ubicacion']);
                @endphp

                <div class="form-group" style="{{ $isFullWidth ? 'grid-column: 1 / -1;' : '' }}">
                    @if($field === 'id_parcela')
                        <label for="{{ $field }}">Parcela *</label>
                        <select name="{{ $field }}" id="{{ $field }}" class="form-control" required>
                            <option value="">Seleccione una parcela</option>
                            @foreach($options['parcelas'] as $p)
                                <option value="{{ $p->id_parcela }}" @selected((string)$val === (string)$p->id_parcela)>
                                    #{{ $p->id_parcela }} - {{ $p->nombre }} ({{ $p->ubicacion ?? 'Sin ubicación' }})
                                </option>
                            @endforeach
                        </select>

                    @elseif($field === 'id_siembra')
                        <label for="{{ $field }}">Siembra *</label>
                        <select name="{{ $field }}" id="{{ $field }}" class="form-control" required>
                            <option value="">Seleccione una siembra</option>
                            @foreach($options['siembras'] as $s)
                                <option value="{{ $s->id_siembra }}" @selected((string)$val === (string)$s->id_siembra)>
                                    #{{ $s->id_siembra }} - {{ $s->variedad_uva }} (Parcela: {{ $s->parcela?->nombre ?? 'N/A' }})
                                </option>
                            @endforeach
                        </select>

                    @elseif($field === 'id_produccion')
                        <label for="{{ $field }}">Producción *</label>
                        <select name="{{ $field }}" id="{{ $field }}" class="form-control" required>
                            <option value="">Seleccione una producción</option>
                            @foreach($options['producciones'] as $pr)
                                <option value="{{ $pr->id_produccion }}" @selected((string)$val === (string)$pr->id_produccion)>
                                    #{{ $pr->id_produccion }} - {{ $pr->nombre_vino }} ({{ $pr->variedad_uva }})
                                </option>
                            @endforeach
                        </select>

                    @elseif($field === 'id_fase')
                        <label for="{{ $field }}">Fase de Producción *</label>
                        <select name="{{ $field }}" id="{{ $field }}" class="form-control" required>
                            <option value="">Seleccione una fase</option>
                            @foreach($options['fases'] as $f)
                                <option value="{{ $f->id_fase }}" @selected((string)$val === (string)$f->id_fase)>
                                    Fase {{ $f->orden_fase }}: {{ $f->nombre }}
                                </option>
                            @endforeach
                        </select>

                    @elseif(isset($enums[$field]))
                        <label for="{{ $field }}">{{ $label }} *</label>
                        <select name="{{ $field }}" id="{{ $field }}" class="form-control" required>
                            @foreach($enums[$field] as $opt)
                                <option value="{{ $opt }}" @selected((string)$val === (string)$opt)>
                                    {{ $opt }}
                                </option>
                            @endforeach
                        </select>

                    @elseif(in_array($field, ['riego_realizado', 'fertilizacion_realizada']))
                        <label>{{ $label }}</label>
                        <div class="form-check">
                            <input type="checkbox" name="{{ $field }}" id="{{ $field }}" value="1" @checked($val)>
                            <label for="{{ $field }}" style="margin: 0; font-weight: normal; cursor: pointer;">
                                Marcar como realizado
                            </label>
                        </div>

                    @elseif(in_array($field, ['descripcion', 'observaciones', 'tratamiento']))
                        <label for="{{ $field }}">{{ $label }}</label>
                        <textarea name="{{ $field }}" id="{{ $field }}" class="form-control" placeholder="Ingrese {{ strtolower($label) }}...">{{ $val }}</textarea>

                    @elseif(str_starts_with($field, 'fecha_'))
                        @php
                            $isDateTime = ($resource === 'seguimiento_fases' && ($field === 'fecha_inicio' || $field === 'fecha_fin'));
                            $formattedVal = $val;
                            if ($val) {
                                if ($isDateTime) {
                                    $formattedVal = is_string($val) ? str_replace(' ', 'T', substr($val, 0, 16)) : $val->format('Y-m-d\TH:i');
                                } else {
                                    $formattedVal = is_string($val) ? substr($val, 0, 10) : $val->format('Y-m-d');
                                }
                            }
                        @endphp
                        <label for="{{ $field }}">{{ $label }} {{ str_contains($field, 'fin') ? '' : '*' }}</label>
                        <input type="{{ $isDateTime ? 'datetime-local' : 'date' }}"
                               name="{{ $field }}"
                               id="{{ $field }}"
                               value="{{ $formattedVal }}"
                               class="form-control"
                               {{ str_contains($field, 'fin') ? '' : 'required' }}>

                    @elseif(in_array($field, ['cantidad_plantas', 'cantidad_plantas_afectadas']))
                        <label for="{{ $field }}">{{ $label }} *</label>
                        <input type="number" name="{{ $field }}" id="{{ $field }}" value="{{ $val }}" min="1" step="1" class="form-control" required>

                    @elseif(in_array($field, ['ph', 'humedad', 'nitrogeno', 'fosforo', 'potasio', 'puntuacion', 'cantidad_uva_kg', 'cantidad_vino_litros']))
                        <label for="{{ $field }}">{{ $label }}</label>
                        <input type="number" name="{{ $field }}" id="{{ $field }}" value="{{ $val }}" step="0.01" class="form-control" {{ in_array($field, ['puntuacion', 'cantidad_uva_kg', 'cantidad_vino_litros']) ? 'required' : '' }}>

                    @else
                        <label for="{{ $field }}">{{ $label }} {{ in_array($field, ['nombre', 'variedad_uva', 'nombre_enfermedad', 'nombre_vino']) ? '*' : '' }}</label>
                        <input type="text" name="{{ $field }}" id="{{ $field }}" value="{{ $val }}" class="form-control" {{ in_array($field, ['nombre', 'variedad_uva', 'nombre_enfermedad', 'nombre_vino']) ? 'required' : '' }}>
                    @endif
                </div>
            @endforeach
        </div>

        <div style="margin-top: 1.5rem; display: flex; gap: 0.75rem; justify-content: flex-end;">
            <a href="{{ route($resource . '.index') }}" class="btn btn-secondary">Cancelar</a>
            <button type="submit" class="btn btn-primary">{{ $record ? 'Guardar Cambios' : 'Registrar' }}</button>
        </div>
    </form>
</div>
@endsection
