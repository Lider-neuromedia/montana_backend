<?php

namespace App\Http\Controllers;

use App\Entities\Tienda;
use App\Entities\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class TiendaController extends Controller
{
    public function clienteTiendas(User $cliente)
    {
        $user = auth()->user();
        $tiendas = [];

        if ($user->rol_id == 3) {
            $tiendas = $user->tiendas()->get();
        } else {
            $tiendas = $cliente->tiendas()->get();
        }

        return response()->json($tiendas, 200);
    }

    /**
     * Obtener detalle de tienda.
     */
    public function show($id)
    {
        $user = auth()->user();
        $tienda = null;

        if ($user->rol_id == 3) {
            $tienda = $user->tiendas()
                ->where('id_tiendas', $id)
                ->with('propietario', 'vendedores')
                ->firstOrFail();
        } else {
            $tienda = Tienda::query()
                ->where('id_tiendas', $id)
                ->with('propietario', 'vendedores')
                ->firstOrFail();
        }

        return response()->json($tienda, 200);
    }

    public function nuevaTienda(Request $request, User $cliente)
    {
        $request->validate([
            'nombre' => ['required', 'string', 'max:60'],
            'lugar' => ['required', 'string', 'max:40'],
            'local' => ['nullable', 'string', 'max:30'],
            'direccion' => ['nullable', 'string', 'max:60'],
            'telefono' => ['nullable', 'string', 'max:40'],
            'sucursal' => ['nullable', 'string', 'max:1'],
            'fecha_ingreso' => ['required', 'date_format:Y-m-d'],
            'fecha_ultima_compra' => ['required', 'date_format:Y-m-d'],
            'cupo' => ['required', 'numeric', 'min:0'],
            'ciudad_codigo' => ['required', 'numeric'],
            'zona' => ['nullable', 'numeric', 'min:0'],
            'bloqueado' => ['required', 'string', 'in:N,S'],
            'bloqueado_fecha' => ['nullable', 'date_format:Y-m-d'],
            'nombre_representante' => ['nullable', 'string', 'max:80'],
            'plazo' => ['required', 'integer', 'min:0'],
            'escala_factura' => ['nullable', 'string', 'max:1'],
            'observaciones' => ['nullable', 'string', 'max:2000'],
            'vendedores' => ['required', 'array', 'min:1'],
            'vendedores.*' => ['required', 'integer', 'exists:users,id'],
        ]);

        try {

            \DB::beginTransaction();

            $tiendaId = $this->saveOrUpdateTienda($request->all(), $cliente);

            \DB::commit();

            return response()->json([
                'tienda_id' => $tiendaId,
                'response' => 'success',
                'status' => 200,
            ], 200);

        } catch (\Exception $ex) {
            \Log::info($ex->getMessage());
            \Log::info($ex->getTraceAsString());
            \DB::rollBack();

            return response()->json([
                'response' => 'error',
                'message' => $ex->getMessage(),
                'status' => 500,
            ], 500);
        }
    }

    /**
     * Guardar multiples tiendas.
     */
    public function store(Request $request)
    {
        $request->validate([
            'cliente' => ['required', 'exists:users,id'],
            'tiendas' => ['required', 'array', 'min:1'],
            'tiendas.*.nombre' => ['required', 'string', 'max:60'],
            'tiendas.*.lugar' => ['required', 'string', 'max:40'],
            'tiendas.*.local' => ['nullable', 'string', 'max:30'],
            'tiendas.*.direccion' => ['nullable', 'string', 'max:60'],
            'tiendas.*.telefono' => ['nullable', 'string', 'max:40'],
            'tiendas.*.sucursal' => ['nullable', 'string', 'max:1'],
            'tiendas.*.fecha_ingreso' => ['required', 'date_format:Y-m-d'],
            'tiendas.*.fecha_ultima_compra' => ['required', 'date_format:Y-m-d'],
            'tiendas.*.cupo' => ['required', 'numeric', 'min:0'],
            'tiendas.*.ciudad_codigo' => ['required', 'numeric'],
            'tiendas.*.zona' => ['nullable', 'numeric', 'min:0'],
            'tiendas.*.bloqueado' => ['required', 'string', 'in:N,S'],
            'tiendas.*.bloqueado_fecha' => ['nullable', 'date_format:Y-m-d'],
            'tiendas.*.nombre_representante' => ['nullable', 'string', 'max:80'],
            'tiendas.*.plazo' => ['required', 'integer', 'min:0'],
            'tiendas.*.escala_factura' => ['nullable', 'string', 'max:1'],
            'tiendas.*.observaciones' => ['nullable', 'string', 'max:2000'],
            'tiendas.*.vendedores' => ['required', 'array', 'min:1'],
            'tiendas.*.vendedores.*' => ['required', 'integer', 'exists:users,id'],
        ]);

        try {

            \DB::beginTransaction();

            $cliente = User::findOrFail($request->get('cliente'));
            $tiendas_ids = [];

            foreach ($request->get('tiendas') as $tiendaData) {
                $tiendas_ids[] = $this->saveOrUpdateTienda($tiendaData, $cliente);
            }

            \DB::commit();

            return response()->json([
                'tiendas_ids' => $tiendas_ids,
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

    public function update(Request $request, Tienda $tienda)
    {
        $request->validate([
            'nombre' => ['required', 'string', 'max:60'],
            'lugar' => ['required', 'string', 'max:40'],
            'local' => ['nullable', 'string', 'max:30'],
            'direccion' => ['nullable', 'string', 'max:60'],
            'telefono' => ['nullable', 'string', 'max:40'],
            'sucursal' => ['nullable', 'string', 'max:1'],
            'fecha_ingreso' => ['required', 'date_format:Y-m-d'],
            'fecha_ultima_compra' => ['required', 'date_format:Y-m-d'],
            'cupo' => ['required', 'numeric', 'min:0'],
            'ciudad_codigo' => ['required', 'numeric'],
            'zona' => ['nullable', 'numeric', 'min:0'],
            'bloqueado' => ['required', 'string', 'in:N,S'],
            'bloqueado_fecha' => ['nullable', 'date_format:Y-m-d'],
            'nombre_representante' => ['nullable', 'string', 'max:80'],
            'plazo' => ['required', 'integer', 'min:0'],
            'escala_factura' => ['nullable', 'string', 'max:1'],
            'observaciones' => ['nullable', 'string', 'max:2000'],
            'vendedores' => ['required', 'array', 'min:1'],
            'vendedores.*' => ['required', 'integer', 'exists:users,id'],
        ]);

        try {

            \DB::beginTransaction();

            $cliente = auth()->user();

            if ($cliente->rol_id == 3 && $tienda->propietario->id != $cliente->id) {
                throw new \Exception("No tiene permiso para actualizar la tienda seleccionada.", 403);
            }

            $tiendaId = $this->saveOrUpdateTienda($request->all(), $cliente, $tienda);

            \DB::commit();

            return response()->json([
                'tienda_id' => $tiendaId,
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
                'message' => 'Error al actualizar la tienda.',
            ], 500);
        }
    }

    private function saveOrUpdateTienda($data, User $cliente, Tienda $tienda = null)
    {
        $tiendaData = [
            'nombre' => $data['nombre'],
            'lugar' => $data['lugar'],
            'local' => $data['local'] ?: '',
            'direccion' => $data['direccion'] ?: '',
            'telefono' => $data['telefono'] ?: '',
            'sucursal' => $data['sucursal'] ?: '',
            'fecha_ingreso' => $data['fecha_ingreso'] ?: Carbon::now()->format('Y-m-d'),
            'fecha_ultima_compra' => $data['fecha_ultima_compra'] ?: Carbon::now()->format('Y-m-d'),
            'cupo' => $data['cupo'],
            'ciudad_codigo' => $data['ciudad_codigo'],
            'zona' => $data['zona'] ?: '',
            'bloqueado' => $data['bloqueado'] ?: 'N',
            'bloqueado_fecha' => $data['bloqueado_fecha'] ?: null,
            'nombre_representante' => $data['nombre_representante'] ?: '',
            'plazo' => $data['plazo'],
            'escala_factura' => $data['escala_factura'] ?: '',
            'observaciones' => $data['observaciones'] ?: '',
        ];

        if ($tienda == null) {
            $tienda = new Tienda($tiendaData);
            $tienda->propietario()->associate($cliente);
            $tienda->save();
        } else {
            $tienda->update($tiendaData);
            $tienda->propietario()->associate($cliente);
            $tienda->save();
        }

        $tienda->vendedores()->sync($data['vendedores']);
        $cliente->vendedores()->syncWithoutDetaching($data['vendedores']);

        return $tienda->id_tiendas;
    }

    /**
     * Eliminar tienda.
     */
    public function eliminarTiendas(Request $request)
    {
        $request->validate([
            'tiendas' => ['required', 'array', 'min:1'],
            'tiendas.*' => ['required', 'integer', 'exists:tiendas,id_tiendas'],
        ]);

        try {

            \DB::beginTransaction();

            foreach ($request->get('tiendas') as $tiendaId) {
                $tienda = Tienda::findOrFail($tiendaId);
                $tiendaTienePedidos = $tienda->detallesProductos()->count() > 0;

                if ($tiendaTienePedidos) {
                    $nombreTienda = "{$tienda->nombre}, {$tienda->lugar}";
                    throw new \Exception("La tienda #$nombreTienda no se puede eliminar ya que tiene pedidos anclados.", 403);
                }

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
                'message' => 'Error al borrar la(s) tienda(s). ' . $ex->getMessage(),
            ], 500);
        }
    }
}
