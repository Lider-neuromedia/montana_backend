<?php

namespace App\Http\Controllers;

use App\Entities\Catalogo;
use App\Entities\Marca;
use App\Entities\Pedido;
use App\Entities\Producto;
use App\Entities\Tienda;
use App\Entities\User;
use Illuminate\Http\Request;

class OfflineController extends Controller
{
    public function imagenes(Request $request)
    {
        $url = url('');

        $imagenesProductos = \DB::table('galeria_productos')
            ->select(\DB::raw("TRIM(CONCAT(\"$url/\", image)) as url"))
            ->get()
            ->pluck('url')
            ->unique()
            ->toArray();

        $imagenesCatalogos = \DB::table('catalogos')
            ->select(\DB::raw("TRIM(CONCAT(\"$url/\", imagen)) as url"))
            ->get()
            ->pluck('url')
            ->unique()
            ->toArray();

        $imagenes = array_merge($imagenesProductos, $imagenesCatalogos);
        return response()->json($imagenes, 200);
    }

    public function catalogos()
    {
        $catalogos = Catalogo::query()
            ->where('estado', 'activo')
            ->where('cantidad', '!=', 0)
            ->orderBy('titulo', 'asc')
            ->get()
            ->map(function ($x) {
                $x->imagen = url($x->imagen);
                return $x;
            });

        return response()->json($catalogos, 200);
    }

    public function productos(Request $request)
    {
        $productos = Producto::query()
            ->whereHas('productoCatalogo', function ($q) {
                $q->where('estado', 'activo');
            })
            ->where("nombre", "!=", "")
            ->where('precio', '>', 0)
            ->with('productoMarca', 'imagenes')
            ->orderBy('nombre', 'asc')
            ->get()
            ->map(function ($x) {
                $x->marca_id = $x->marca;
                $x->marca = $x->productoMarca;
                unset($x->productoMarca);

                $x->image = null;

                $x->imagenes = $x->imagenes->map(function ($y) {
                    $y->id = $y->id_galeria_prod;
                    $y->image = url($y->image);
                    unset($y->id_galeria_prod);
                    return $y;
                });

                // Cargar imagen destacada.
                if ($x->imagenes->isNotEmpty()) {
                    $tempImage = $x->imagenes->firstWhere('destacada', 1);
                    $x->image = $tempImage != null ? $tempImage->image : null;
                }

                // Si no hay imagen destacada, cargar cualquier imagen.
                if ($x->image == null) {
                    $tempImage = $x->imagenes->first();
                    $x->image = $tempImage != null ? $tempImage->image : null;
                }

                return $x;
            });

        return response()->json($productos, 200);
    }

    public function pedidos(Request $request)
    {
        $user = auth()->user();

        $pedidos = Pedido::query()
            ->when($user->rol_id == 3, function ($q) use ($user) {
                $q->where('cliente', $user->id);
            })
            ->when($user->rol_id == 2, function ($q) use ($user) {
                $q->where('vendedor', $user->id);
            })
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
            ->orderBy('fecha', 'desc')
            ->get()
            ->map(function ($x) {
                if ($x->firma != null) {
                    $x->firma = url($x->firma);
                }

                $x->estado_id = $x->pedidoEstado->id_estado;
                $x->estado = [
                    'id' => $x->pedidoEstado->id_estado,
                    'estado' => $x->pedidoEstado->estado,
                ];
                unset($x->pedidoEstado);

                $x->vendedor_id = $x->pedidoVendedor->id;
                $x->vendedor = $x->pedidoVendedor;
                unset($x->pedidoVendedor);

                $x->cliente_id = $x->pedidoCliente->id;
                $x->cliente = $x->pedidoCliente;
                unset($x->pedidoCliente);

                $cliente_id = $x->cliente_id;

                $x->detalles = $x->detalles->map(function ($y) use ($cliente_id) {
                    $y->referencia = $y->detalleProducto->referencia;
                    $y->lugar = $y->detalleTienda->lugar;

                    // Detalle
                    $y->id = $y->id_pedido_prod;
                    unset($y->id_pedido_prod);

                    // Pedido
                    $y->pedido_id = $y->pedido;
                    unset($y->pedido);

                    // Producto
                    $y->producto_id = $y->producto;
                    $producto = $y->detalleProducto;
                    $catalogo_id = intval("{$y->detalleProducto->catalogo}");
                    $marca_id = intval("{$y->detalleProducto->marca}");
                    $producto->catalogo_id = $catalogo_id;
                    $producto->marca_id = $marca_id;
                    $y->producto = $producto;

                    // Tienda
                    $y->tienda_id = $y->tienda;
                    $tienda = $y->detalleTienda;
                    $tienda->cliente_id = $cliente_id;
                    $y->tienda = $tienda;

                    unset($tienda->cliente);
                    unset($producto->catalogo);
                    unset($producto->marca);
                    unset($y->detalleProducto);
                    unset($y->detalleTienda);
                    unset($y->producto->catalogo);
                    unset($y->producto->marca);
                    unset($y->tienda->cliente);

                    return $y;
                });

                return $x;
            });

        return response()->json($pedidos, 200);
    }

    public function clientes(Request $request)
    {
        $user = auth()->user();

        $clientes = User::query()
            ->where('rol_id', 3)
            ->when($user->rol_id == 2, function ($q) use ($user) {
                $q->whereHas('vendedores', function ($q) use ($user) {
                    $q->where('id', $user->id);
                });
            })
            ->with('datos')
            ->get();

        return response()->json($clientes, 200);
    }

    public function tiendas(Request $request)
    {
        $request->validate([
            'clientes_ids' => ['required', 'array', 'min:1'],
            'clientes_ids.*' => ['required', 'integer', 'exists:users,id'],
        ]);

        $user = auth()->user();

        $tiendas = Tienda::query()
            ->whereIn('cliente', $request->get('clientes_ids'))
            ->when($user->rol_id == 2, function ($q) use ($user) {
                $q->whereHas('propietario', function ($q) use ($user) {
                    $q->whereHas('vendedores', function ($q) use ($user) {
                        $q->where('id', $user->id);
                    });
                });
            })
            ->with('propietario', 'vendedores')
            ->get()
            ->map(function ($x) {
                $x->cliente_id = $x->cliente;
                $x->cliente = $x->propietario;
                unset($x->propietario);
                return $x;
            });

        return response()->json($tiendas, 200);
    }

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

        if ($tienda != null);{
            $tienda->cliente_id = $tienda->cliente;
            $tienda->cliente = $tienda->propietario;
            unset($tienda->propietario);
        }

        return response()->json($tienda, 200);
    }
}
