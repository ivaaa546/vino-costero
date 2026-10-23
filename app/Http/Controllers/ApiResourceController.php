<?php

namespace App\Http\Controllers;

use App\Models\Enologia\FaseProduccion;
use App\Models\Enologia\Produccion;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;

class ApiResourceController extends Controller
{
    private function authorizeApi(array $roles): void
    {
        $user = auth()->user();
        if (!$user) {
            abort(response()->json(['code' => 401, 'message' => 'No autenticado'], 401));
        }

        $roleId = (int) $user->id_rol;
        if ($roleId !== 1 && !in_array($roleId, $roles, true)) {
            abort(response()->json(['code' => 403, 'message' => 'No autorizado'], 403));
        }
    }

    public function index(Request $request, string $resource)
    {
        $def = ResourceController::definition($resource);
        $this->authorizeApi($def['roles']);

        $modelClass = $def['model'];
        $key = $def['key'];

        $query = $modelClass::query();
        if (!empty($def['with'])) {
            $query->with($def['with']);
        }

        // Si se pasa un id específico
        $id = $request->input($key) ?? $request->input('id');
        if ($id) {
            $record = $query->where($key, $id)->first();
            if (!$record) {
                return response()->json(['code' => 404, 'message' => 'El registro no existe'], 404);
            }
            return response()->json([
                'code' => 200,
                'message' => 'Registro obtenido correctamente',
                $resource => $record,
            ]);
        }

        // Búsqueda opcional
        $search = trim((string) $request->input('buscar', ''));
        if ($search !== '') {
            $this->applySearch($query, $resource, $search);
        }

        $records = $query->orderByDesc($key)->get();

        $response = [
            'code' => 200,
            'message' => 'Registros obtenidos correctamente',
            $resource => $records,
        ];

        // Datos complementarios según recurso
        if ($resource === 'seguimiento_fases') {
            $response['fases'] = FaseProduccion::where('estado', 'Activa')->orderBy('orden_fase')->get();
        } elseif ($resource === 'catas') {
            $response['producciones'] = Produccion::select(['id_produccion', 'nombre_vino'])->orderByDesc('id_produccion')->get();
        }

        return response()->json($response);
    }

    public function show(Request $request, string $resource, int $id)
    {
        $def = ResourceController::definition($resource);
        $this->authorizeApi($def['roles']);

        $modelClass = $def['model'];
        $query = $modelClass::query();
        if (!empty($def['with'])) {
            $query->with($def['with']);
        }

        $record = $query->findOrFail($id);

        return response()->json([
            'code' => 200,
            'message' => 'Registro obtenido correctamente',
            $resource => $record,
        ]);
    }

    public function store(Request $request, string $resource)
    {
        $def = ResourceController::definition($resource);
        $this->authorizeApi($def['roles']);

        $rules = ResourceController::validationRules($resource);
        $validated = $request->validate($rules);

        if ($resource === 'controles_siembra') {
            $validated['riego_realizado'] = $request->boolean('riego_realizado');
            $validated['fertilizacion_realizada'] = $request->boolean('fertilizacion_realizada');
        }

        foreach ($validated as $k => $v) {
            if (is_string($v) && trim($v) === '') {
                $validated[$k] = null;
            }
        }

        $modelClass = $def['model'];
        $record = new $modelClass();
        $record->fill($validated);
        $record->save();

        return response()->json([
            'code' => 201,
            'message' => 'Registro creado correctamente',
            'id' => $record->getKey(),
            $resource => $record,
        ], 201);
    }

    public function update(Request $request, string $resource, ?int $id = null)
    {
        $def = ResourceController::definition($resource);
        $this->authorizeApi($def['roles']);

        $id = $id ?? (int) ($request->input($def['key']) ?? $request->input('id'));
        if (!$id) {
            return response()->json(['code' => 400, 'message' => 'ID no válido'], 400);
        }

        $modelClass = $def['model'];
        $record = $modelClass::findOrFail($id);

        $rules = ResourceController::validationRules($resource, $id);
        $validated = $request->validate($rules);

        if ($resource === 'controles_siembra') {
            $validated['riego_realizado'] = $request->boolean('riego_realizado');
            $validated['fertilizacion_realizada'] = $request->boolean('fertilizacion_realizada');
        }

        foreach ($validated as $k => $v) {
            if (is_string($v) && trim($v) === '') {
                $validated[$k] = null;
            }
        }

        $record->fill($validated);
        $record->save();

        return response()->json([
            'code' => 200,
            'message' => 'Registro actualizado correctamente',
            'id' => $id,
            $resource => $record,
        ]);
    }

    public function destroy(Request $request, string $resource, ?int $id = null)
    {
        $def = ResourceController::definition($resource);
        $this->authorizeApi($def['roles']);

        $id = $id ?? (int) ($request->input($def['key']) ?? $request->input('id'));
        if (!$id) {
            return response()->json(['code' => 400, 'message' => 'ID no válido'], 400);
        }

        $modelClass = $def['model'];

        try {
            $record = $modelClass::findOrFail($id);
            $record->delete();

            return response()->json([
                'code' => 200,
                'message' => 'Registro eliminado correctamente',
                'id' => $id,
            ]);
        } catch (QueryException $e) {
            return response()->json([
                'code' => 409,
                'message' => 'El registro tiene datos relacionados y no puede ser eliminado',
            ], 409);
        }
    }

    private function applySearch($query, string $resource, string $search): void
    {
        switch ($resource) {
            case 'parcelas':
                $query->where('nombre', 'ilike', "%{$search}%");
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
                $query->where('nombre_enfermedad', 'ilike', "%{$search}%");
                break;
            case 'producciones':
                $query->where('nombre_vino', 'ilike', "%{$search}%");
                break;
            case 'seguimiento_fases':
                $query->whereHas('produccion', fn($q) => $q->where('nombre_vino', 'ilike', "%{$search}%"));
                break;
            case 'catas':
                $query->whereHas('produccion', fn($q) => $q->where('nombre_vino', 'ilike', "%{$search}%"));
                break;
        }
    }
}
