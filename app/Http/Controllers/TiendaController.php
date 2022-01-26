<?php

namespace App\Http\Controllers;

use App\Entities\Tienda;
use DB;
use Illuminate\Http\Request;

class TiendaController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'cliente' => ['required', 'exists:users,id'],
            'tiendas' => ['required', 'array', 'min:1'],
            'tiendas.*.nombre' => ['required', 'string', 'max:45'],
            'tiendas.*.lugar' => ['required', 'string', 'max:45'],
            'tiendas.*.local' => ['nullable', 'string', 'max:20'],
            'tiendas.*.direccion' => ['nullable', 'string', 'max:50'],
            'tiendas.*.telefono' => ['nullable', 'string', 'max:20'],
        ]);

        try {

            \DB::beginTransaction();

            foreach ($request['tiendas'] as $tienda) {
                \DB::table('tiendas')->insert([
                    'nombre' => $tienda['nombre'],
                    'lugar' => $tienda['lugar'],
                    'local' => $tienda['local'],
                    'direccion' => $tienda['direccion'],
                    'telefono' => $tienda['telefono'],
                    'cliente' => $request['cliente'],
                ]);
            }

            \DB::commit();

            return response()->json([
                'response' => 'success',
                'status' => 200,
            ], 200);

        } catch (\Exception $ex) {
            \Log::info($ex->getMessage());
            \Log::info($ex->getTraceAsString());
            \DB::rollBack();

            return response()->json([
                'response' => 'success',
                'status' => 500,
                'message' => 'Error al guardar la(s) tienda(s).',
            ], 500);
        }
    }

    public function show($id)
    {
        $user = auth()->user();
        $tienda = Tienda::query()
            ->where('id_tiendas', $id)
            ->when($user->rol_id == 3, function ($q) use ($user) {
                $q->where('cliente', $user->id);
            })
            ->first();

        return response()->json($tienda, 200);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nombre' => ['required', 'string', 'max:45'],
            'lugar' => ['required', 'string', 'max:45'],
            'local' => ['nullable', 'string', 'max:20'],
            'direccion' => ['nullable', 'string', 'max:50'],
            'telefono' => ['nullable', 'string', 'max:20'],
        ]);

        $user = auth()->user();

        if ($user->rol_id == 3) {
            $tiene_tienda = \DB::table('tiendas')
                ->where('id_tiendas', $id)
                ->where('cliente', $user->id)
                ->exists();

            if (!$tiene_tienda) {
                throw new \Exception("No tiene permiso para actualizar la tienda seleccionada.", 403);
            }
        }

        $tienda = Tienda::findOrFail($id);
        $tienda->update([
            'nombre' => $request['nombre'],
            'lugar' => $request['lugar'],
            'local' => $request['local'],
            'direccion' => $request['direccion'],
            'telefono' => $request['telefono'],
        ]);

        return response()->json([
            'response' => 'success',
            'status' => 200,
        ]);
    }

    public function destroy(Request $request)
    {
        $request->validate([
            'tiendas' => ['required', 'array', 'min:1'],
            'tiendas.*' => ['required', 'exists:tiendas,id_tiendas'],
        ]);

        try {

            \DB::beginTransaction();

            foreach ($request['tiendas'] as $tienda) {
                $pedido = DB::table('pedido_productos')
                    ->where('tienda', $tienda)
                    ->exists();

                if ($pedido) {
                    throw new \Exception("La tienda #{$tienda} no se puede eliminar ya que tiene un pedido anclado.", 403);
                }

                $tienda = Tienda::findOrFail($tienda);
                $tienda->delete();
            }

            \DB::commit();

            return response()->json([
                'response' => 'success',
                'status' => 200,
                'message' => "Tienda(s) eliminada(s).",
            ], 200);

        } catch (\Exception $ex) {
            \Log::info($ex->getMessage());
            \Log::info($ex->getTraceAsString());
            \DB::rollBack();

            return response()->json([
                'response' => 'success',
                'status' => 500,
                'message' => 'Error al borrar la(s) tienda(s).',
            ], 500);
        }
    }
}
