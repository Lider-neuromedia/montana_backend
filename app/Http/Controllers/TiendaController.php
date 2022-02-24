<?php

namespace App\Http\Controllers;

use App\Entities\Tienda;
use App\Entities\User;
use Illuminate\Http\Request;

class TiendaController extends Controller
{
    public function clienteTiendas($cliente_id)
    {
        $user = auth()->user();
        $tiendas = [];

        if ($user->rol_id == 3) {
            $tiendas = $user->tiendas()->get();
        } else {
            $tiendas = Tienda::query()
                ->where('cliente', $cliente_id)
                ->get();
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

    public function nuevaTienda(Request $request, $cliente)
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

        $tiendaData = $request->only('nombre', 'lugar', 'direccion', 'local', 'telefono',
            'nombre', 'lugar', 'local', 'direccion', 'telefono', 'sucursal', 'fecha_ingreso',
            'fecha_ultima_compra', 'cupo', 'ciudad_codigo', 'zona', 'bloqueado', 'bloqueado_fecha',
            'nombre_representante', 'plazo', 'escala_factura', 'observaciones');

        $tiendaData['local'] = $tiendaData['local'] ?: '';
        $tiendaData['telefono'] = $tiendaData['telefono'] ?: '';
        $tiendaData['direccion'] = $tiendaData['direccion'] ?: '';
        $tiendaData['sucursal'] = $tiendaData['sucursal'] ?: '';
        $tiendaData['zona'] = $tiendaData['zona'] ?: '';
        $tiendaData['nombre_representante'] = $tiendaData['nombre_representante'] ?: '';
        $tiendaData['escala_factura'] = $tiendaData['escala_factura'] ?: '';
        $tiendaData['observaciones'] = $tiendaData['observaciones'] ?: '';
        $tiendaData['bloqueado_fecha'] = $tiendaData['bloqueado_fecha'] ?: null;

        $cliente = User::findOrFail($cliente);
        $tienda = new Tienda($tiendaData);
        $tienda->propietario()->associate($cliente);
        $tienda->save();

        $tienda->vendedores()->sync($request->get('vendedores'));
        $cliente->vendedores()->syncWithoutDetaching($request->get('vendedores'));

        return response()->json([
            'response' => 'success',
            'status' => 200,
        ], 200);
    }

    /**
     * Guardar tiendas.
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

            $cliente = User::query()
                ->where('rol_id', 3)
                ->where('id', $request->get('cliente'))
                ->firstOrFail();

            foreach ($request->get('tiendas') as $tiendaData) {
                $tienda = new Tienda([
                    'nombre' => $tiendaData['nombre'],
                    'lugar' => $tiendaData['lugar'],
                    'local' => $tiendaData['local'] ?: '',
                    'direccion' => $tiendaData['direccion'] ?: '',
                    'telefono' => $tiendaData['telefono'] ?: '',
                    'sucursal' => $tiendaData['sucursal'] ?: '',
                    'fecha_ingreso' => $tiendaData['fecha_ingreso'],
                    'fecha_ultima_compra' => $tiendaData['fecha_ultima_compra'],
                    'cupo' => $tiendaData['cupo'],
                    'ciudad_codigo' => $tiendaData['ciudad_codigo'],
                    'zona' => $tiendaData['zona'] ?: '',
                    'bloqueado' => $tiendaData['bloqueado'],
                    'bloqueado_fecha' => $tiendaData['bloqueado_fecha'] ?: null,
                    'nombre_representante' => $tiendaData['nombre_representante'] ?: '',
                    'plazo' => $tiendaData['plazo'],
                    'escala_factura' => $tiendaData['escala_factura'] ?: '',
                    'observaciones' => $tiendaData['observaciones'] ?: '',
                ]);
                $tienda->propietario()->associate($cliente);
                $tienda->save();

                $tienda->vendedores()->sync($tiendaData['vendedores']);
                $cliente->vendedores()->syncWithoutDetaching($tiendaData['vendedores']);
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

    public function update(Request $request, $id)
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

            $user = auth()->user();
            $tienda = Tienda::findOrFail($id);

            if ($user->rol_id == 3 && $tienda->propietario->id != $user->id) {
                throw new \Exception("No tiene permiso para actualizar la tienda seleccionada.", 403);
            }

            $tiendaData = $request->only('nombre', 'lugar', 'direccion', 'local', 'telefono',
                'nombre', 'lugar', 'local', 'direccion', 'telefono', 'sucursal', 'fecha_ingreso',
                'fecha_ultima_compra', 'cupo', 'ciudad_codigo', 'zona', 'bloqueado', 'bloqueado_fecha',
                'nombre_representante', 'plazo', 'escala_factura', 'observaciones');

            $tiendaData['local'] = $tiendaData['local'] ?: '';
            $tiendaData['telefono'] = $tiendaData['telefono'] ?: '';
            $tiendaData['direccion'] = $tiendaData['direccion'] ?: '';
            $tiendaData['sucursal'] = $tiendaData['sucursal'] ?: '';
            $tiendaData['zona'] = $tiendaData['zona'] ?: '';
            $tiendaData['nombre_representante'] = $tiendaData['nombre_representante'] ?: '';
            $tiendaData['escala_factura'] = $tiendaData['escala_factura'] ?: '';
            $tiendaData['observaciones'] = $tiendaData['observaciones'] ?: '';
            $tiendaData['bloqueado_fecha'] = $tiendaData['bloqueado_fecha'] ?: null;

            $tienda->update($tiendaData);
            $tienda->vendedores()->sync($request->get('vendedores'));
            $user->vendedores()->syncWithoutDetaching($request->get('vendedores'));

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
                'message' => 'Error al actualizar la tienda.',
            ], 500);
        }
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
                'message' => 'Error al borrar la(s) tienda(s).',
            ], 500);
        }
    }
}
