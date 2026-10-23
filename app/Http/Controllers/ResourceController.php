<?php

namespace App\Http\Controllers;

use App\Models\Agronomia\ControlSiembra;
use App\Models\Agronomia\ControlTierra;
use App\Models\Agronomia\Parcela;
use App\Models\Agronomia\Siembra;
use App\Models\Agronomia\SiembraEnferma;
use App\Models\Enologia\Cata;
use App\Models\Enologia\FaseProduccion;
use App\Models\Enologia\Produccion;
use App\Models\Enologia\SeguimientoFase;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ResourceController extends Controller
{
    public const RESOURCES = [
        'parcelas' => [
            'model' => Parcela::class,
            'key' => 'id_parcela',
            'title' => 'Parcelas',
            'roles' => [1, 3, 4],
            'fields' => ['nombre', 'ubicacion', 'descripcion', 'estado'],
            'with' => [],
        ],
        'controles_tierra' => [
            'model' => ControlTierra::class,
            'key' => 'id_control_tierra',
            'title' => 'Controles de Tierra',
            'roles' => [1, 3, 4],
            'fields' => ['id_parcela', 'fecha_control', 'ph', 'humedad', 'nitrogeno', 'fosforo', 'potasio', 'observaciones'],
            'with' => ['parcela'],
        ],
        'siembras' => [
            'model' => Siembra::class,
            'key' => 'id_siembra',
            'title' => 'Siembras',
            'roles' => [1, 3, 4],
            'fields' => ['id_parcela', 'variedad_uva', 'fecha_siembra', 'cantidad_plantas', 'estado', 'observaciones'],
            'with' => ['parcela'],
        ],
        'controles_siembra' => [
            'model' => ControlSiembra::class,
            'key' => 'id_control_siembra',
            'title' => 'Controles de Siembra',
            'roles' => [1, 3, 4],
            'fields' => ['id_siembra', 'fecha_control', 'estado_general', 'riego_realizado', 'fertilizacion_realizada', 'observaciones'],
            'with' => ['siembra.parcela'],
        ],
        'siembras_enfermas' => [
            'model' => SiembraEnferma::class,
            'key' => 'id_siembra_enferma',
            'title' => 'Siembras Enfermas',
            'roles' => [1, 3, 4],
            'fields' => ['id_siembra', 'nombre_enfermedad', 'fecha_deteccion', 'cantidad_plantas_afectadas', 'nivel_gravedad', 'tratamiento', 'estado', 'observaciones'],
            'with' => ['siembra.parcela'],
        ],
        'producciones' => [
            'model' => Produccion::class,
            'key' => 'id_produccion',
            'title' => 'Producciones',
            'roles' => [1, 2, 4],
            'fields' => ['id_parcela', 'nombre_vino', 'variedad_uva', 'fecha_inicio', 'fecha_fin', 'cantidad_uva_kg', 'cantidad_vino_litros', 'estado', 'observaciones'],
            'with' => ['parcela'],
        ],
        'seguimiento_fases' => [
            'model' => SeguimientoFase::class,
            'key' => 'id_seguimiento',
            'title' => 'Seguimiento de Fases',
            'roles' => [1, 2, 4],
            'fields' => ['id_produccion', 'id_fase', 'fecha_inicio', 'fecha_fin', 'estado', 'observaciones'],
            'with' => ['produccion', 'fase'],
        ],
        'catas' => [
            'model' => Cata::class,
            'key' => 'id_cata',
            'title' => 'Catas',
            'roles' => [1, 2, 4],
            'fields' => ['id_produccion', 'fecha_cata', 'aroma', 'sabor', 'color', 'cuerpo', 'puntuacion', 'resultado', 'observaciones'],
            'with' => ['produccion'],
        ],
    ];

    public static function definition(string $resource): array
    {
        abort_unless(isset(self::RESOURCES[$resource]), 404, 'Recurso no encontrado: ' . $resource);
        return self::RESOURCES[$resource];
    }

    private function resolveResource(Request $request, ?string $resource = null): string
    {
        if ($resource && isset(self::RESOURCES[$resource])) {
            return $resource;
        }

        $fromRoute = (string) $request->route('resource');
        if ($fromRoute && isset(self::RESOURCES[$fromRoute])) {
            return $fromRoute;
        }

        $segment = (string) $request->segment(1);
        if ($segment === 'resources') {
            $segment = (string) $request->segment(2);
        }

        abort_unless(isset(self::RESOURCES[$segment]), 404, 'Recurso no encontrado: ' . $segment);
        return $segment;
    }

    private function resolveId(Request $request, mixed $param1 = null, mixed $param2 = null): int
    {
        if (is_numeric($param2)) {
            return (int) $param2;
        }
        if (is_numeric($param1)) {
            return (int) $param1;
        }
        $fromRoute = $request->route('id');
        if (is_numeric($fromRoute)) {
            return (int) $fromRoute;
        }
        $segment = $request->segment(2);
        if (is_numeric($segment)) {
            return (int) $segment;
        }
        $segment3 = $request->segment(3);
        if (is_numeric($segment3)) {
            return (int) $segment3;
        }

        abort(400, 'ID no válido');
    }

    private function authorizeResource(string $resource): void
    {
        $def = self::definition($resource);
        $user = auth()->user();
        abort_unless($user, 401);

        $roleId = (int) $user->id_rol;
        abort_unless($roleId === 1 || in_array($roleId, $def['roles'], true), 403, 'No autorizado para acceder a este recurso.');
    }

    public function index(Request $request, ?string $resource = null)
    {
        $resource = $this->resolveResource($request, $resource);
        $this->authorizeResource($resource);

        $def = self::definition($resource);
        $modelClass = $def['model'];
        $key = $def['key'];
        $title = $def['title'];
        $fields = $def['fields'];

        $query = $modelClass::query();
        if (!empty($def['with'])) {
            $query->with($def['with']);
        }

        $search = trim((string) $request->input('buscar', ''));
        if ($search !== '') {
            $this->applySearch($query, $resource, $search);
        }

        $records = $query->orderByDesc($key)->paginate(15)->withQueryString();

        return view('resources.index', compact('resource', 'title', 'fields', 'records', 'key', 'search'));
    }

    public function create(Request $request, ?string $resource = null)
    {
        $resource = $this->resolveResource($request, $resource);
        $this->authorizeResource($resource);

        return view('resources.form', $this->formData($resource));
    }

    public function edit(Request $request, mixed $param1 = null, mixed $param2 = null)
    {
        $resource = $this->resolveResource($request, is_string($param1) && !is_numeric($param1) ? $param1 : null);
        $id = $this->resolveId($request, $param1, $param2);
        $this->authorizeResource($resource);

        $def = self::definition($resource);
        $modelClass = $def['model'];

        $data = $this->formData($resource);
        $data['record'] = $modelClass::findOrFail($id);

        return view('resources.form', $data);
    }

    public function store(Request $request, ?string $resource = null)
    {
        $resource = $this->resolveResource($request, $resource);
        $this->authorizeResource($resource);

        $def = self::definition($resource);
        $modelClass = $def['model'];

        $validated = $this->validateResource($request, $resource);

        $record = new $modelClass();
        $record->fill($validated);
        $record->save();

        return redirect()->route($resource . '.index')->with('status', 'Registro creado correctamente.');
    }

    public function update(Request $request, mixed $param1 = null, mixed $param2 = null)
    {
        $resource = $this->resolveResource($request, is_string($param1) && !is_numeric($param1) ? $param1 : null);
        $id = $this->resolveId($request, $param1, $param2);
        $this->authorizeResource($resource);

        $def = self::definition($resource);
        $modelClass = $def['model'];

        $record = $modelClass::findOrFail($id);
        $validated = $this->validateResource($request, $resource, $id);

        $record->fill($validated);
        $record->save();

        return redirect()->route($resource . '.index')->with('status', 'Registro actualizado correctamente.');
    }

    public function destroy(Request $request, mixed $param1 = null, mixed $param2 = null)
    {
        $resource = $this->resolveResource($request, is_string($param1) && !is_numeric($param1) ? $param1 : null);
        $id = $this->resolveId($request, $param1, $param2);
        $this->authorizeResource($resource);

        $def = self::definition($resource);
        $modelClass = $def['model'];

        try {
            $record = $modelClass::findOrFail($id);
            $record->delete();

            return redirect()->route($resource . '.index')->with('status', 'Registro eliminado correctamente.');
        } catch (QueryException $e) {
            return back()->withErrors(['resource' => 'No se puede eliminar el registro porque tiene datos relacionados o en uso.']);
        }
    }

    private function applySearch($query, string $resource, string $search): void
    {
        switch ($resource) {
            case 'parcelas':
                $query->where('nombre', 'ilike', "%{$search}%")
                      ->orWhere('ubicacion', 'ilike', "%{$search}%");
                break;
            case 'controles_tierra':
                $query->whereHas('parcela', fn($q) => $q->where('nombre', 'ilike', "%{$search}%"));
                break;
            case 'siembras':
                $query->where('variedad_uva', 'ilike', "%{$search}%")
                      ->orWhereHas('parcela', fn($q) => $q->where('nombre', 'ilike', "%{$search}%"));
                break;
            case 'controles_siembra':
                $query->whereHas('siembra', fn($q) => $q->where('variedad_uva', 'ilike', "%{$search}%"));
                break;
            case 'siembras_enfermas':
                $query->where('nombre_enfermedad', 'ilike', "%{$search}%")
                      ->orWhereHas('siembra', fn($q) => $q->where('variedad_uva', 'ilike', "%{$search}%"));
                break;
            case 'producciones':
                $query->where('nombre_vino', 'ilike', "%{$search}%")
                      ->orWhere('variedad_uva', 'ilike', "%{$search}%");
                break;
            case 'seguimiento_fases':
                $query->whereHas('produccion', fn($q) => $q->where('nombre_vino', 'ilike', "%{$search}%"))
                      ->orWhereHas('fase', fn($q) => $q->where('nombre', 'ilike', "%{$search}%"));
                break;
            case 'catas':
                $query->whereHas('produccion', fn($q) => $q->where('nombre_vino', 'ilike', "%{$search}%"))
                      ->orWhere('resultado', 'ilike', "%{$search}%");
                break;
        }
    }

    private function formData(string $resource): array
    {
        $def = self::definition($resource);

        $options = [
            'parcelas' => Parcela::orderBy('nombre')->get(),
            'siembras' => Siembra::with('parcela')->orderByDesc('id_siembra')->get(),
            'producciones' => Produccion::orderByDesc('id_produccion')->get(),
            'fases' => FaseProduccion::where('estado', 'Activa')->orderBy('orden_fase')->get(),
        ];

        $enums = [
            'parcelas' => [
                'estado' => ['Activa', 'Inactiva'],
            ],
            'siembras' => [
                'estado' => ['En crecimiento', 'En producción', 'Finalizada', 'Suspendida'],
            ],
            'controles_siembra' => [
                'estado_general' => ['Excelente', 'Bueno', 'Regular', 'Malo'],
            ],
            'siembras_enfermas' => [
                'nivel_gravedad' => ['Leve', 'Moderada', 'Grave'],
                'estado' => ['Detectada', 'En tratamiento', 'Controlada', 'Sin recuperacion'],
            ],
            'producciones' => [
                'estado' => ['Planificada', 'En proceso', 'Finalizada', 'Cancelada'],
            ],
            'seguimiento_fases' => [
                'estado' => ['Pendiente', 'En proceso', 'Completada', 'Cancelada'],
            ],
            'catas' => [
                'resultado' => ['Aprobado', 'Requiere mejoras', 'Rechazado'],
            ],
        ];

        return [
            'resource' => $resource,
            'title' => $def['title'],
            'fields' => $def['fields'],
            'key' => $def['key'],
            'record' => null,
            'options' => $options,
            'enums' => $enums[$resource] ?? [],
        ];
    }

    public static function validationRules(string $resource, ?int $id = null): array
    {
        switch ($resource) {
            case 'parcelas':
                return [
                    'nombre' => ['required', 'string', 'max:80', Rule::unique('parcelas', 'nombre')->ignore($id, 'id_parcela')],
                    'ubicacion' => ['nullable', 'string', 'max:150'],
                    'descripcion' => ['nullable', 'string'],
                    'estado' => ['required', 'in:Activa,Inactiva'],
                ];

            case 'controles_tierra':
                return [
                    'id_parcela' => ['required', 'integer', 'exists:parcelas,id_parcela'],
                    'fecha_control' => ['required', 'date'],
                    'ph' => ['nullable', 'numeric', 'between:0,14'],
                    'humedad' => ['nullable', 'numeric', 'between:0,100'],
                    'nitrogeno' => ['nullable', 'numeric', 'min:0'],
                    'fosforo' => ['nullable', 'numeric', 'min:0'],
                    'potasio' => ['nullable', 'numeric', 'min:0'],
                    'observaciones' => ['nullable', 'string'],
                ];

            case 'siembras':
                return [
                    'id_parcela' => ['required', 'integer', 'exists:parcelas,id_parcela'],
                    'variedad_uva' => ['required', 'string', 'max:80'],
                    'fecha_siembra' => ['required', 'date'],
                    'cantidad_plantas' => ['required', 'integer', 'min:1'],
                    'estado' => ['required', 'in:En crecimiento,En producción,Finalizada,Suspendida'],
                    'observaciones' => ['nullable', 'string'],
                ];

            case 'controles_siembra':
                return [
                    'id_siembra' => ['required', 'integer', 'exists:siembras,id_siembra'],
                    'fecha_control' => ['required', 'date'],
                    'estado_general' => ['required', 'in:Excelente,Bueno,Regular,Malo'],
                    'riego_realizado' => ['nullable', 'boolean'],
                    'fertilizacion_realizada' => ['nullable', 'boolean'],
                    'observaciones' => ['nullable', 'string'],
                ];

            case 'siembras_enfermas':
                return [
                    'id_siembra' => ['required', 'integer', 'exists:siembras,id_siembra'],
                    'nombre_enfermedad' => ['required', 'string', 'max:120'],
                    'fecha_deteccion' => ['required', 'date'],
                    'cantidad_plantas_afectadas' => ['required', 'integer', 'min:1'],
                    'nivel_gravedad' => ['required', 'in:Leve,Moderada,Grave'],
                    'tratamiento' => ['nullable', 'string'],
                    'estado' => ['required', 'in:Detectada,En tratamiento,Controlada,Sin recuperacion'],
                    'observaciones' => ['nullable', 'string'],
                ];

            case 'producciones':
                return [
                    'id_parcela' => ['required', 'integer', 'exists:parcelas,id_parcela'],
                    'nombre_vino' => ['required', 'string', 'max:100'],
                    'variedad_uva' => ['required', 'string', 'max:80'],
                    'fecha_inicio' => ['required', 'date'],
                    'fecha_fin' => ['nullable', 'date', 'after_or_equal:fecha_inicio'],
                    'cantidad_uva_kg' => ['required', 'numeric', 'min:0'],
                    'cantidad_vino_litros' => ['required', 'numeric', 'min:0'],
                    'estado' => ['required', 'in:Planificada,En proceso,Finalizada,Cancelada'],
                    'observaciones' => ['nullable', 'string'],
                ];

            case 'seguimiento_fases':
                return [
                    'id_produccion' => ['required', 'integer', 'exists:producciones,id_produccion'],
                    'id_fase' => ['required', 'integer', 'exists:fases_produccion,id_fase'],
                    'fecha_inicio' => ['required', 'date'],
                    'fecha_fin' => ['nullable', 'date', 'after_or_equal:fecha_inicio'],
                    'estado' => ['required', 'in:Pendiente,En proceso,Completada,Cancelada'],
                    'observaciones' => ['nullable', 'string'],
                ];

            case 'catas':
                return [
                    'id_produccion' => ['required', 'integer', 'exists:producciones,id_produccion'],
                    'fecha_cata' => ['required', 'date'],
                    'aroma' => ['nullable', 'string', 'max:200'],
                    'sabor' => ['nullable', 'string', 'max:200'],
                    'color' => ['nullable', 'string', 'max:100'],
                    'cuerpo' => ['nullable', 'string', 'max:100'],
                    'puntuacion' => ['required', 'numeric', 'between:0,100'],
                    'resultado' => ['required', 'in:Aprobado,Requiere mejoras,Rechazado'],
                    'observaciones' => ['nullable', 'string'],
                ];

            default:
                return [];
        }
    }

    private function validateResource(Request $request, string $resource, ?int $id = null): array
    {
        $rules = self::validationRules($resource, $id);
        $data = $request->validate($rules);

        if ($resource === 'controles_siembra') {
            $data['riego_realizado'] = $request->boolean('riego_realizado');
            $data['fertilizacion_realizada'] = $request->boolean('fertilizacion_realizada');
        }

        foreach ($data as $k => $v) {
            if (is_string($v) && trim($v) === '') {
                $data[$k] = null;
            }
        }

        return $data;
    }
}
