<?php

namespace App\Http\Controllers;

use App\Entities\Catalogo;
use App\Entities\Detalle;
use App\Entities\Estado;
use App\Entities\Novedad;
use App\Entities\Pedido;
use App\Entities\Producto;
use App\Entities\Tienda;
use App\Entities\User;
use App\Exports\PedidoExport;
use Carbon\Carbon;
use Excel;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PedidoController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search') ?: false;
        $sort = $request->get('sort') ?: false;
        $date = $request->get('date') ?: false;
        $user = auth()->user();

        $pedidos = Pedido::query()
            ->when($user->rol_id == 3, function ($q) use ($user) {
                $q->where('cliente', $user->id);
            })
            ->when($user->rol_id == 2, function ($q) use ($user) {
                $q->where('vendedor', $user->id);
            })
            ->when($date == 'hoy', function ($q) {
                $q->whereDate('fecha', Carbon::now()->format('Y-m-d'));
            })
            ->when($date == 'ayer', function ($q) {
                $q->whereDate('fecha', Carbon::now()->sub('days', 1)->format('Y-m-d'));
            })
            ->when($search, function ($q) use ($search) {
                $q->where(function ($q) use ($search) {
                    $q->where('codigo', 'like', "%$search%")
                        ->orWhere('total', 'like', "%$search%")
                        ->orWhereHas('pedidoVendedor', function ($q) use ($search) {
                            $q->where('name', 'like', "%$search%")
                                ->orWhere('apellidos', 'like', "%$search%")
                                ->orWhere('email', 'like', "%$search%")
                                ->orWhere('dni', 'like', "%$search%");
                        })
                        ->orWhereHas('pedidoCliente', function ($q) use ($search) {
                            $q->where('name', 'like', "%$search%")
                                ->orWhere('apellidos', 'like', "%$search%")
                                ->orWhere('email', 'like', "%$search%")
                                ->orWhere('dni', 'like', "%$search%");
                        });
                });
            })
            ->with('pedidoVendedor', 'pedidoCliente', 'pedidoEstado')
            ->when($sort, function ($q) use ($sort) {
                if ($sort == 'recientes') {
                    $q->orderBy('fecha', 'desc');
                } else if ($sort == 'ultimos') {
                    $q->orderBy('fecha', 'asc');
                } else if ($sort == 'entregados') {
                    $q->orderBy('estado', 'asc');
                } else if ($sort == 'cancelados') {
                    $q->orderBy('estado', 'desc');
                }
            })
            ->when(!$sort, function ($q) {
                $q->orderBy('fecha', 'desc');
            })
            ->paginate(20);

        $pedidos->setCollection(
            $pedidos->getCollection()
                ->map(function ($x) {
                    $x->vendedor_id = intval("{$x->vendedor}");
                    $x->cliente_id = intval("{$x->cliente}");
                    $x->estado_id = intval("{$x->pedidoEstado->id_estado}");

                    $x->vendedor = [
                        'id' => $x->vendedor,
                        'name' => $x->pedidoVendedor->nombre_completo,
                    ];
                    $x->cliente = [
                        'id' => $x->cliente,
                        'name' => $x->pedidoCliente->nombre_completo,
                    ];
                    $x->estado = [
                        'id' => $x->pedidoEstado->id_estado,
                        'estado' => $x->pedidoEstado->estado,
                    ];

                    if ($x->firma != null) {
                        $x->firma = url($x->firma);
                    }

                    unset($x->sub_total);
                    unset($x->metodo_pago);
                    unset($x->descuento);
                    unset($x->notas);
                    unset($x->notas_facturacion);
                    unset($x->pedidoVendedor);
                    unset($x->pedidoCliente);
                    unset($x->pedidoEstado);
                    return $x;
                })
        );

        return response()->json([
            'pedidos' => $pedidos,
            'response' => 'success',
            'status' => 200,
        ], 200);
    }

    public function show(Pedido $pedido)
    {
        $pedido = Pedido::query()
            ->where('id_pedido', $pedido->id_pedido)
            ->with(
                'pedidoVendedor',
                'pedidoVendedor.datos',
                'pedidoCliente',
                'pedidoCliente.datos',
                'pedidoEstado',
                'detalles',
                'detalles.detalleProducto',
                'detalles.detalleTienda',
                'novedades')
            ->firstOrFail();

        if ($pedido->firma != null) {
            $pedido->firma = url($pedido->firma);
        }

        $pedido->estado_id = $pedido->pedidoEstado->id_estado;
        $pedido->estado = [
            'id' => $pedido->pedidoEstado->id_estado,
            'estado' => $pedido->pedidoEstado->estado,
        ];
        unset($pedido->pedidoEstado);

        $pedido->vendedor_id = $pedido->pedidoVendedor->id;
        $pedido->vendedor = $pedido->pedidoVendedor;
        unset($pedido->pedidoVendedor);

        $pedido->cliente_id = $pedido->pedidoCliente->id;
        $pedido->cliente = $pedido->pedidoCliente;
        unset($pedido->pedidoCliente);

        $cliente_id = $pedido->cliente_id;

        $pedido->detalles = $pedido->detalles->map(function ($x) use ($cliente_id) {
            $x->referencia = $x->detalleProducto->referencia;
            $x->lugar = $x->detalleTienda->lugar;

            // Detalle
            $x->id = $x->id_pedido_prod;
            unset($x->id_pedido_prod);

            // Pedido
            $x->pedido_id = $x->pedido;
            unset($x->pedido);

            // Producto
            $x->producto_id = $x->producto;
            $producto = $x->detalleProducto;
            $catalogo_id = intval("{$x->detalleProducto->catalogo}");
            $marca_id = intval("{$x->detalleProducto->marca}");
            $producto->catalogo_id = $catalogo_id;
            $producto->marca_id = $marca_id;
            $x->producto = $producto;

            // Tienda
            $x->tienda_id = $x->tienda;
            $tienda = $x->detalleTienda;
            $tienda->cliente_id = $cliente_id;
            $x->tienda = $tienda;

            unset($tienda->cliente);
            unset($producto->catalogo);
            unset($producto->marca);
            unset($x->detalleProducto);
            unset($x->detalleTienda);
            unset($x->producto->catalogo);
            unset($x->producto->marca);
            unset($x->tienda->cliente);

            return $x;
        });

        return response()->json([
            'status' => 200,
            'response' => 'success',
            'pedido' => $pedido,
        ], 200);
    }

    public function store(Request $request)
    {
        $request->validate([
            'codigo_pedido' => ['required', 'unique:pedidos,codigo'],
            'cliente' => ['required', 'integer', 'exists:users,id'],
            'vendedor' => ['required', 'integer', 'exists:users,id'],
            'descuento' => ['required', 'integer', 'min:0'],
            'metodo_pago' => ['required', 'string', 'max:100', 'in:contado,credito'],
            'total' => ['required', 'integer', 'min:0'],
            'notas' => ['nullable', 'string', 'max:250'],
            'notas_facturacion' => ['nullable', 'string', 'max:250'],
            'firma' => ['required', 'file', 'max:2000'],
            'productos' => ['required', 'array', 'min:1'],
            'productos.*.producto_id' => [
                'required',
                Rule::exists('productos', 'id_producto')->whereNull('deleted_at'),
            ],
            'productos.*.tiendas' => ['required', 'array', 'min:1'],
            'productos.*.tiendas.*.cantidad_producto' => ['required', 'integer', 'min:1'],
            'productos.*.tiendas.*.id_tienda' => ['required', 'exists:tiendas,id_tiendas'],
        ]);

        return $this->saveOrUpdate($request);
    }

    public function update(Request $request, Pedido $pedido)
    {
        $request->validate([
            'cliente' => ['required', 'integer', 'exists:users,id'],
            'vendedor' => ['required', 'integer', 'exists:users,id'],
            'descuento' => ['required', 'integer', 'min:0'],
            'metodo_pago' => ['required', 'string', 'max:100', 'in:contado,credito'],
            'total' => ['required', 'integer', 'min:0'],
            'notas' => ['nullable', 'string', 'max:250'],
            'notas_facturacion' => ['nullable', 'string', 'max:250'],
            'firma' => ['nullable', 'file', 'max:2000'],
            'productos' => ['required', 'array', 'min:1'],
            'productos.*.producto_id' => [
                'required',
                Rule::exists('productos', 'id_producto')->whereNull('deleted_at'),
            ],
            'productos.*.tiendas' => ['required', 'array', 'min:1'],
            'productos.*.tiendas.*.cantidad_producto' => ['required', 'integer', 'min:1'],
            'productos.*.tiendas.*.id_tienda' => ['required', 'exists:tiendas,id_tiendas'],
        ]);

        return $this->saveOrUpdate($request, $pedido);
    }

    private function saveOrUpdate(Request $request, Pedido $pedido = null)
    {
        try {

            \DB::beginTransaction();

            $esNuevoPedido = $pedido == null;

            $pedidoData = $request->only('metodo_pago', 'total', 'notas', 'notas_facturacion', 'descuento');
            $pedidoData['sub_total'] = $request->get('total');

            $cliente = User::findOrFail($request->get('cliente'));
            $vendedor = User::findOrFail($request->get('vendedor'));

            // Guardar firma.
            if ($request->hasFile('firma')) {
                if (!$esNuevoPedido && $pedido->firma) { // Borrar firma actual.
                    $file = array_reverse(explode('/', $pedido->firma))[0];
                    \Storage::delete("public/firmas/$file");
                }

                $path = $request->file('firma')->store('public/firmas');
                $pedidoData['firma'] = str_replace('public/firmas', 'storage/firmas', $path);
            }

            // Calcular descuento.
            $descuento = $pedidoData['descuento'];
            $total = $pedidoData['total'];

            if ($descuento > 0) {
                $pedidoData['total'] = $total - ($total * ($descuento / 100));
            }

            if ($esNuevoPedido) {
                $pedidoData['fecha'] = Carbon::now()->format('Y-m-d');
                $pedidoData['codigo'] = $request->get('codigo_pedido');
                $estado = Estado::findOrFail(2);

                $pedido = new Pedido($pedidoData);
                $pedido->pedidoCliente()->associate($cliente);
                $pedido->pedidoVendedor()->associate($vendedor);
                $pedido->pedidoEstado()->associate($estado);
                $pedido->save();
            } else {
                $pedido->update($pedidoData);
            }

            // Reiniciar stock de productos.
            if (!$esNuevoPedido) {
                foreach ($pedido->detalles as $detalle) {
                    $cantidad = $detalle->cantidad_producto;
                    $producto = $detalle->detalleProducto;

                    if ($producto != null) {
                        $producto->update([
                            'stock' => $producto->stock + $cantidad,
                        ]);
                    }
                }

                $pedido->detalles()->delete();
            }

            // Productos
            foreach ($request['productos'] as $productoData) {
                $producto = Producto::findOrFail($productoData['producto_id']);

                foreach ($productoData['tiendas'] as $tiendaData) {
                    $tienda = $cliente->tiendas()->findOrFail($tiendaData['id_tienda']);
                    $cantidad = $tiendaData['cantidad_producto'];

                    $detalle = new Detalle([
                        'cantidad_producto' => $cantidad,
                    ]);
                    $detalle->detallePedido()->associate($pedido);
                    $detalle->detalleProducto()->associate($producto);
                    $detalle->detalleTienda()->associate($tienda);
                    $detalle->save();

                    // Reducir stock
                    $producto->update([
                        'stock' => $producto->stock - $cantidad,
                    ]);
                }

                if ($producto->stock < 0) {
                    throw new \Exception("No hay stock suficiente en el producto {$producto->id_producto}.", 1);
                }
            }

            \DB::commit();

            return response()->json([
                'status' => 200,
                'response' => 'success',
                'message' => 'Pedido guardado correctamente.',
                'pedido_id' => $pedido->id_pedido,
            ], 200);

        } catch (\Exception $ex) {
            \Log::info($ex->getMessage());
            \Log::info($ex->getTraceAsString());
            \DB::rollBack();

            return response()->json([
                'status' => 500,
                'response' => 'error',
                'message' => $ex->getMessage(),
            ], 500);
        }
    }

    public function exportPedido()
    {
        return Excel::download(new PedidoExport(), 'pedidos.xlsx');
    }

    public function pedidoPorCodigo($codigo)
    {
        $pedido = Pedido::query()
            ->select('id_pedido', 'fecha', 'codigo', 'descuento', 'total', 'ven.name AS name_vendedor', 'cliente', 'vendedor',
                'ven.apellidos AS apellido_vendedor', 'cli.name AS name_cliente', 'cli.apellidos AS apellido_cliente', 'estado')
            ->join('users AS ven', 'vendedor', '=', 'ven.id')
            ->join('users AS cli', 'cliente', '=', 'cli.id')
            ->where('codigo', $codigo)
            ->firstOrFail();

        return response()->json([
            'response' => 'success',
            'status' => 200,
            'pedido' => $pedido,
        ], 200);
    }

    public function cambiarDescuentoPedido(Pedido $pedido, $descuento)
    {
        $descuentoValor = intval($descuento);

        if ("$descuento" != "$descuentoValor") {
            throw new \Exception("Valor de descuento no valido", 1);

        }

        $pedido->descuento = $descuento;
        $pedido->total = $pedido->sub_total - ($pedido->sub_total * ($descuento / 100));
        $pedido->save();

        return response()->json([
            'message' => 'Descuento actualizado',
            'response' => 'success',
            'status' => 200,
        ], 200);
    }

    public function recursosCrearPedido()
    {
        $vendedores = User::query()
            ->select('id', \DB::raw('TRIM(CONCAT(name, " ", apellidos)) as name'))
            ->where('rol_id', 2)
            ->get();

        $clientes = User::query()
            ->select('id', \DB::raw('TRIM(CONCAT(name, " ", apellidos)) as name'))
            ->where('rol_id', 3)
            ->get();

        $catalogos = Catalogo::query()
            ->where('estado', 'activo')
            ->where('cantidad', '!=', 0)
            ->get()
            ->map(function ($x) {
                $x->imagen = url($x->imagen);
                return $x;
            });

        return response()->json([
            'vendedores' => $vendedores,
            'clientes' => $clientes,
            'catalogos' => $catalogos,
            'response' => 'success',
            'status' => 200,
        ], 200);
    }

    public function generarCodigoPedido()
    {
        $codigo = null;

        do {

            $codigo = uniqid();
            $yaExisteCodigo = Pedido::where('codigo', $codigo)->exists();

        } while ($yaExisteCodigo);

        return response()->json([
            'code' => $codigo,
            'response' => 'success',
            'status' => 200,
        ], 200);
    }

    public function cambiarEstadoPedido(Request $request)
    {
        $request->validate([
            'pedido' => ['required', 'integer', 'exists:pedidos,id_pedido'],
            'state' => ['required', 'integer', 'exists:estados,id_estado'],
        ]);

        $estado = Estado::findOrFail($request['state']);
        $pedido = Pedido::findOrFail($request['pedido']);
        $pedido->pedidoEstado()->associate($estado);
        $pedido->save();

        return response()->json([
            'status' => 200,
            'response' => 'success',
            'message' => 'Actualizado.',
        ], 200);
    }

    public function crearNovedad(Request $request)
    {
        $request->validate([
            'tipo' => ['required', 'string', 'max:250'],
            'descripcion' => ['required', 'string', 'max:10000'],
            'pedido' => ['required', 'integer', 'exists:pedidos,id_pedido'],
        ]);

        $pedido = Pedido::findOrFail($request->get('pedido'));

        $novedad = new Novedad($request->only('tipo', 'descripcion'));
        $novedad->novedadPedido()->associate($pedido);
        $novedad->save();

        return response()->json([
            'status' => 200,
            'response' => 'success',
            'message' => 'Novedad creada.',
        ], 200);
    }
}
