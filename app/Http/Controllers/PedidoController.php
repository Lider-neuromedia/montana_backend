<?php

namespace App\Http\Controllers;

use App\Entities\Catalogo;
use App\Entities\Novedades;
use App\Entities\Pedido;
use App\Entities\PedidoProduct;
use App\Entities\Producto;
use App\Entities\User;
use App\Exports\PedidoExport;
use DB;
use Excel;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PedidoController extends Controller
{
    public function index(Request $request)
    {
        $search = $request['search'];
        $date = $request['date'];

        $pedidos = Pedido::select('id_pedido', 'fecha', 'firma', 'codigo', 'total', 'ven.name AS name_vendedor',
            'ven.apellidos AS apellido_vendedor', 'cli.name AS name_cliente', 'cli.apellidos AS apellido_cliente', 'estados.estado', 'estados.id_estado')
            ->join('estados', 'pedidos.estado', '=', 'estados.id_estado')
            ->join('users AS ven', 'vendedor', '=', 'ven.id')
            ->join('users AS cli', 'cliente', '=', 'cli.id');

        if ($date == 'hoy') {

            // Estamos la zona horaria en caso de que no funcione la configuracion del servidor.
            date_default_timezone_set('America/Bogota');
            $date_filter = date('Y-m-d');
            $pedidos = $pedidos->orWhere('fecha', '=', $date_filter);

        } else if ($date == 'ayer') {

            date_default_timezone_set('America/Bogota');
            $current_day = date('Y-m-d');
            $date_filter = date("Y-m-d", strtotime($current_day . "- 1 days"));
            $pedidos = $pedidos->orWhere('fecha', '=', $date_filter);

        } else {

            $pedidos = $pedidos->where('codigo', 'like', "%$search%")
                ->orWhere('total', 'like', "%$search%")
                ->orWhere('ven.name', 'like', "%$search%")
                ->orWhere('ven.apellidos', 'like', "%$search%")
                ->orWhere('cli.name', 'like', "%$search%");

        }

        $pedidos = $pedidos->get();

        $response = [
            'response' => 'success',
            'status' => 200,
            'pedidos' => $pedidos,
        ];

        return response()->json($response);
    }

    public function resourcesCreate()
    {
        $vendedores = User::where('rol_id', 2)->get();
        $clientes = User::where('rol_id', 3)->get();
        $catalogos = Catalogo::where('estado', 'activo')->where('cantidad', '!=', 0)->get();

        $response = [
            'response' => 'success',
            'status' => 200,
            'vendedores' => $vendedores,
            'clientes' => $clientes,
            'catalogos' => $catalogos,
        ];

        return response()->json($response);
    }

    public function tiendaCliente($id)
    {
        $tiendas = DB::table('tiendas')->where('cliente', $id)->get();
        return response()->json($tiendas);
    }

    public function generateCodePedido()
    {
        $code = uniqid();
        $validate_code = Pedido::where('codigo', $code)->exists();

        if (!$validate_code) {

            $response = [
                'response' => 'success',
                'status' => 200,
                'code' => $code,
            ];

        } else {

            $response = [
                'response' => 'error',
                'status' => 403,
                'message' => 'El codigo esta registrado. Intenta de nuevo.',
            ];

        }

        return response()->json($response);
    }

    public function store(Request $request)
    {
        $request->validate([
            'cliente' => 'required|integer|exists:users,id',
            'vendedor' => 'required|integer|exists:users,id',
            'codigo_pedido' => 'required|unique:pedidos,codigo',
            'total_pedido' => 'required|numeric',
            'descuento' => 'required|numeric',
            'forma_pago' => 'required|string|max:100|in:contado,credito',
            'notas' => 'string',
            'firma' => 'required|image',
            'productos' => 'required|array',
            'productos.*.id_producto' => [
                'required',
                Rule::exists('productos', 'id_producto')->whereNull('deleted_at'),
            ],
            'productos.*.tiendas' => 'required|array',
            'productos.*.tiendas.*.cantidad' => 'required|integer|min:1',
            'productos.*.tiendas.*.id_tienda' => 'required|exists:tiendas,id_tiendas',
        ]);

        try {

            \DB::beginTransaction();

            $pedido = new Pedido();
            $pedido->fecha = date('Y-m-d');
            $pedido->codigo = $request['codigo_pedido'];
            $pedido->metodo_pago = $request['forma_pago'];
            $pedido->sub_total = $request['total_pedido'];
            $pedido->descuento = $request['descuento'];

            // Guardar firma.
            if ($request->hasFile('firma')) {
                $path = $request->file('firma')->store('public/firmas');
                $public_path = str_replace('public/firmas', 'storage/firmas', $path);
                $pedido->firma = $public_path;
            }

            // Sacamos el descuento si lo tiene.
            if ($request['descuento'] != 0) {
                $descuento = $request['total_pedido'] * ($request['descuento'] / 100);
                $total = $request['total_pedido'] - $descuento;
            } else {
                $total = $request['total_pedido'];
            }

            $pedido->total = $total;
            $pedido->notas = $request['notas'];
            $pedido->vendedor = $request['vendedor'];
            $pedido->cliente = $request['cliente'];
            $pedido->estado = 2;
            $pedido->save();

            // Creamos la relacion con productos.

            foreach ($request['productos'] as $product) {
                foreach ($product['tiendas'] as $tienda) {

                    $pedido_product = new PedidoProduct();
                    $pedido_product->pedido = $pedido->id_pedido;
                    $pedido_product->producto = $product['id_producto'];
                    $pedido_product->cantidad_producto = $tienda['cantidad'];
                    $pedido_product->tienda = $tienda['id_tienda'];
                    $pedido_product->save();

                    // Reducir stock
                    $producto = Producto::findOrFail($product['id_producto']);
                    $producto->stock = $producto->stock - $tienda['cantidad'];
                    $producto->save();

                }
            }

            \DB::commit();

            return response()->json([
                'status' => 200,
                'response' => 'success',
                'message' => 'Pedido creado.',
            ], 200);

        } catch (\Exception $ex) {

            \Log::info($ex->getMessage());
            \Log::info($ex->getTraceAsString());
            \DB::rollBack();

            return response()->json([
                'status' => 500,
                'response' => 'error',
                'message' => 'Error interno del servidor, no se pudo guardar el pedido.',
            ], 500);
        }
    }

    public function show($id)
    {
        $pedido = Pedido::select('id_pedido', 'fecha', 'firma', 'codigo', 'metodo_pago', 'sub_total', 'total',
            'descuento', 'notas', 'vendedor', 'estados.estado', 'id_estado', 'cliente')
            ->join('estados', 'pedidos.estado', '=', 'id_estado')
            ->where('id_pedido', $id)
            ->first();

        // Consulta del cliente asignado.
        $cliente = User::find($pedido->cliente);
        $data_admin = DB::table('user_data')->where('user_id', $pedido->cliente)->first();
        $cliente->nit = $data_admin->value_key;

        $pedido->info_cliente = $cliente;

        // Consulta de productos.
        $productos = PedidoProduct::select('referencia', 'cantidad_producto', 'lugar')
            ->where('pedido', $pedido->id_pedido)
            ->join('productos', 'producto', '=', 'id_producto')
            ->join('tiendas', 'tienda', '=', 'id_tiendas')
            ->get();

        $pedido->productos = $productos;

        // Consulta de novedades.
        $novedades = Novedades::where('pedido', $pedido->id_pedido)->get();
        $pedido->novedades = $novedades;

        $response = [
            'status' => 200,
            'response' => 'success',
            'pedido' => $pedido,
        ];

        return response()->json($response);
    }

    public function changeState(Request $request)
    {
        $pedido = Pedido::find($request['pedido']);
        $pedido->estado = $request['state'];

        if ($pedido->save()) {

            $response = [
                'status' => 200,
                'response' => 'success',
                'message' => 'Actualizado.',
            ];

        } else {

            $response = [
                'status' => 403,
                'response' => 'error',
                'message' => 'error en la actualizacion',
            ];

        }

        return response()->json($response);
    }

    public function storeNovedades(Request $request)
    {
        $request->validate([
            'tipo' => 'required',
            'descripcion' => 'required',
            'pedido' => 'required',
        ]);

        $novedad = new Novedades();
        $novedad->tipo = $request['tipo'];
        $novedad->descripcion = $request['descripcion'];
        $novedad->pedido = $request['pedido'];

        if ($novedad->save()) {

            $response = [
                'status' => 200,
                'response' => 'success',
                'message' => 'Novedad creada.',
            ];

        } else {

            $response = [
                'status' => 403,
                'response' => 'error',
                'message' => 'Error en la creación.',
            ];

        }

        return response()->json($response);

    }

    public function edit($id)
    {
        $pedido = Pedido::select('id_pedido', 'fecha', 'firma', 'codigo', 'metodo_pago', 'sub_total', 'total',
            'descuento', 'notas', 'vendedor', 'estados.estado', 'id_estado', 'cliente')
            ->join('estados', 'pedidos.estado', '=', 'id_estado')
            ->where('id_pedido', $id)
            ->first();

        // Consulta de productos.
        $productos = PedidoProduct::select('pedido', 'producto', 'referencia', 'stock', 'productos.total')
            ->where('pedido', $pedido->id_pedido)
            ->join('productos', 'producto', '=', 'id_producto')
            ->groupBy('producto')
            ->get();

        foreach ($productos as $product) {
            $product->tiendas = PedidoProduct::select('id_pedido_prod', 'cantidad_producto', 'tienda', 'lugar', 'local')
                ->join('tiendas', 'tienda', '=', 'id_tiendas')
                ->where('producto', $product->producto)
                ->get();
        }

        $pedido->productos = $productos;

        $response = [
            'status' => 200,
            'response' => 'success',
            'pedido' => $pedido,
        ];

        return response()->json($response);
    }

    public function update(Request $request)
    {
        $request->validate([
            'id_pedido' => 'required|exists:pedidos,id_pedido',
            'metodo_pago' => 'required|string|max:100|in:contado,credito',
            'total' => 'required|numeric',
            'firma' => 'nullable|image',

            'productos' => 'required|array',
            'productos.*.producto' => [
                'required',
                Rule::exists('productos', 'id_producto')->whereNull('deleted_at'),
            ],
            'productos.*.stock' => 'required|integer|min:1',
            'productos.*.tiendas' => 'required|array',
            'productos.*.tiendas.*.cantidad_producto' => 'required|integer|min:1',
            'productos.*.tiendas.*.id_pedido_prod' => 'required|exists:pedido_productos,id_pedido_prod',
        ]);

        try {

            \DB::beginTransaction();

            $pedido = Pedido::findOrFail($request['id_pedido']);
            $pedido->metodo_pago = $request['metodo_pago'];
            $pedido->sub_total = $request['total'];
            $pedido->total = $request['total'];

            // Guardar firma.
            if ($request->hasFile('firma')) {
                // Borrar firma actual.
                $firma_actual = $pedido->firma;
                if ($firma_actual) {
                    $file = array_reverse(explode('/', $firma_actual))[0];
                    \Storage::delete("public/firmas/$file");
                }

                $path = $request->file('firma')->store('public/firmas');
                $public_path = str_replace('public/firmas', 'storage/firmas', $path);
                $pedido->firma = $public_path;
            }

            $pedido->save();

            foreach ($request['productos'] as $producto) {
                $update_producto = Producto::findOrFail($producto['producto']);
                $update_producto->stock = $producto['stock'];
                $update_producto->save();
                foreach ($producto['tiendas'] as $tiendas) {
                    $pedido_prod = PedidoProduct::findOrFail($tiendas['id_pedido_prod']);
                    $pedido_prod->cantidad_producto = $tiendas['cantidad_producto'];
                    $pedido_prod->save();
                }
            }

            \DB::commit();

            return response()->json([
                'status' => 200,
                'response' => 'success',
                'message' => 'Pedido actualizado.',
            ], 200);

        } catch (\Exception $ex) {

            \Log::info($ex->getMessage());
            \Log::info($ex->getTraceAsString());
            \DB::rollBack();

            return response()->json([
                'status' => 500,
                'response' => 'success',
                'message' => 'Error interno del servidor, no se pudo actualizar el pedido.',
            ], 500);

        }
    }

    public function exportPedido()
    {
        return Excel::download(new PedidoExport(), 'pedido.xlsx');
    }

    public function getPedidoWithCode($code)
    {
        $pedido = Pedido::where('codigo', $code);

        if ($pedido->exists()) {

            $pedido = $pedido->select('id_pedido', 'fecha', 'codigo', 'descuento', 'total', 'ven.name AS name_vendedor', 'cliente', 'vendedor',
                'ven.apellidos AS apellido_vendedor', 'cli.name AS name_cliente', 'cli.apellidos AS apellido_cliente', 'estado')
                ->join('users AS ven', 'vendedor', '=', 'ven.id')
                ->join('users AS cli', 'cliente', '=', 'cli.id')
                ->first();

            $response = [
                'response' => 'success',
                'status' => 200,
                'pedido' => $pedido,
            ];

        } else {

            $response = [
                'response' => 'error',
                'status' => 403,
                'message' => 'El pedido ingresado no existe en base de datos.',
            ];

        }

        return response()->json($response);
    }

    public function changeDescuentoPedido($pedido, $descuento)
    {
        $validate_pedido = Pedido::where('id_pedido', $pedido);

        if ($validate_pedido->exists()) {

            $pedido = Pedido::find($pedido);
            $pedido->descuento = $descuento;
            $pedido->total = $pedido->sub_total - ($pedido->sub_total * ($descuento / 100));
            $pedido->save();

            $response = [
                'response' => 'success',
                'status' => 200,
            ];

        } else {

            $response = [
                'response' => 'error',
                'status' => 403,
                'message' => 'El pedido ingresado no existe en base de datos.',
            ];

        }

        return response()->json($response);
    }
}
