<?php

namespace App\Http\Controllers;

use App\Entities\Catalogo;
use App\Entities\GaleriaProducto;
use App\Entities\Marca;
use App\Entities\Producto;
use App\Http\Requests\ProductoRequest;
use Illuminate\Http\Request;

class ProductoController extends Controller
{
    public function index(Request $request, Catalogo $catalogo)
    {
        $search = $request->get('search') ?: false;

        $productos = $catalogo->productos()
            ->when($search, function ($q) use ($search) {
                $q->where('nombre', 'like', "%$search%")
                    ->orWhere('codigo', 'like', "%$search%")
                    ->orWhere('referencia', 'like', "%$search%")
                    ->orWhere('stock', 'like', "%$search%")
                    ->orWhere('descripcion', 'like', "%$search%")
                    ->orWhere('sku', 'like', "%$search%")
                    ->orWhere('precio', 'like', "%$search%")
                    ->orWhere('total', 'like', "%$search%")
                    ->orWhereHas('productoMarca', function ($q) use ($search) {
                        $q->where('nombre_marca', 'like', "%$search%")
                            ->orWhere('codigo', 'like', "%$search%");
                    });
            })
            ->with([
                'productoMarca',
                'imagenes' => function ($q) {
                    $q->where('destacada', 1);
                },
            ])
            ->orderBy('name', 'asc')
            ->paginate(20);

        $productos->setCollection(
            $productos->getCollection()
                ->map(function ($x) {
                    $x->marca_id = $x->marca;
                    $x->marca = $x->productoMarca;
                    unset($x->productoMarca);

                    $x->image = null;

                    if ($x->imagenes->isNotEmpty()) {
                        $x->image = url($x->imagenes->first()->image);
                    } else {
                        $imagen = $x->imagenes()->first();

                        if ($imagen != null) {
                            $x->image = url($imagen->image);
                        }
                    }

                    unset($x->imagenes);
                    return $x;
                })
        );

        return response()->json([
            'response' => 'success',
            'status' => 200,
            'productos' => $productos,
        ], 200);
    }

    public function show(Producto $producto)
    {
        $producto->marca_id = $producto->marca;
        $producto->marca = $producto->productoMarca;
        unset($producto->productoMarca);

        $producto->image = null;
        $producto->imagenes = $producto->imagenes()
            ->orderBy('destacada', 'desc')
            ->get()
            ->map(function ($x) {
                $x->id = $x->id_galeria_prod;
                unset($x->id_galeria_prod);
                $x->image = url($x->image);
                return $x;
            });

        if ($producto->imagenes->isNotEmpty()) {
            $producto->image = $producto->imagenes->first()->image;
        }

        return response()->json([
            'producto' => $producto,
            'response' => 'success',
            'status' => 200,
        ], 200);
    }

    public function store(ProductoRequest $request)
    {
        return $this->saveOrUpdate($request);
    }

    public function update(ProductoRequest $request, Producto $producto)
    {
        return $this->saveOrUpdate($request, $producto);
    }

    public function saveOrUpdate(Request $request, Producto $producto = null)
    {
        try {

            \DB::beginTransaction();

            $esNuevoProducto = $producto == null;
            $marca = Marca::findOrFail($request->get('marca_id'));
            $catalogo = Catalogo::findOrFail($request->get('catalogo_id'));
            $productoData = $request->only('nombre', 'codigo', 'referencia',
                'stock', 'sku', 'descripcion', 'precio', 'total', 'iva');

            if ($producto == null) {
                $producto = new Producto($productoData);
            } else {
                $producto->update($productoData);
            }

            $producto->productoMarca()->associate($marca);
            $producto->productoCatalogo()->associate($catalogo);
            $producto->save();

            if ($esNuevoProducto) {
                $catalogo->cantidad++;
                $catalogo->save();
            }

            $imagenes = $request->get('imagenes') ?: [];

            // Borrar imagenes que ya no están.
            if (!$esNuevoProducto) {
                $imagenesActuales = $producto->imagenes()->get();

                foreach ($imagenesActuales as $x) {
                    $borrarImagen = true;
                    $esImagenActual = isset($imagen['id']) && $imagen['id'];

                    foreach ($imagenes as $imagen) {
                        if ($esImagenActual && $x->id_galeria_prod == $imagen['id']) {
                            $borrarImagen = false;
                        }
                    }

                    if ($esImagenActual && $borrarImagen) {
                        $path_image = public_path($x->image);

                        if (file_exists($path_image)) {
                            unlink($path_image);
                        }

                        $x->delete();
                    }
                }
            }

            foreach ($imagenes as $index => $imagen) {
                $imagenFile = null;

                if (isset($request->file('imagenes')[$index])) {
                    $imagenFile = $request->file('imagenes')[$index]['file'];
                }

                // Crear imagen nueva.
                if ($imagenFile != null) {
                    $name = "{$producto->referencia}-{$index}";

                    $filename = $this->saveImage(
                        $imagenFile,
                        $name,
                        $producto->catalogo,
                        $producto->referencia);

                    $ruta = "storage/productos/{$producto->catalogo}/{$producto->referencia}/{$filename}";

                    $producto->imagenes()->save(new GaleriaProducto([
                        'name_img' => $name,
                        'producto' => $producto->id_producto,
                        'image' => $ruta,
                        'destacada' => $imagen['destacada'],
                    ]));
                }

                // Actualizar imagen actual.
                if (isset($imagen['id'])) {
                    $producto->imagenes()
                        ->findOrFail($imagen['id'])
                        ->update([
                            'destacada' => $imagen['destacada'],
                        ]);
                }
            }

            Catalogo::refrescarCantidadDeProductos($catalogo);

            \DB::commit();

            return response()->json([
                'message' => "Producto guardado correctamente.",
                'response' => 'success',
                'producto_id' => $producto->id_producto,
                'status' => 200,
            ], 200);

        } catch (\Exception $ex) {
            \Log::info($ex->getMessage());
            \Log::info($ex->getTraceAsString());
            \DB::rollBack();

            return response()->json([
                'message' => $ex->getMessage(),
                'response' => 'error',
                'status' => 500,
            ], 500);
        }
    }

    public function destroy(Producto $producto)
    {
        if ($producto->detalles()->exists()) {
            throw new \Exception("No se puede borrar el producto, tiene pedidos asignados.");
        }

        $catalogo = $producto->productoCatalogo;
        $catalogo->cantidad--;
        $catalogo->save();

        $producto->delete();

        Catalogo::refrescarCantidadDeProductos($catalogo);

        return response()->json([
            'response' => 'success',
            'status' => 200,
            'message' => "Producto eliminado.",
        ], 200);
    }

    public function productosShowRoom(Request $request)
    {
        $search = $request->get('search') ?: false;

        $catalogo = Catalogo::query()
            ->where('tipo', 'show room')
            ->where('estado', 'activo')
            ->firstOrFail();

        $productos = $catalogo->productos()
            ->when($search, function ($q) use ($search) {
                $q->where('nombre', 'like', "%$search%")
                    ->orWhere('codigo', 'like', "%$search%")
                    ->orWhere('referencia', 'like', "%$search%")
                    ->orWhere('stock', 'like', "%$search%")
                    ->orWhere('descripcion', 'like', "%$search%")
                    ->orWhere('sku', 'like', "%$search%")
                    ->orWhere('precio', 'like', "%$search%")
                    ->orWhere('total', 'like', "%$search%")
                    ->orWhereHas('productoMarca', function ($q) use ($search) {
                        $q->where('nombre_marca', 'like', "%$search%")
                            ->orWhere('codigo', 'like', "%$search%");
                    });
            })
            ->with([
                'productoMarca',
                'imagenes' => function ($q) {
                    $q->where('destacada', 1);
                },
            ])
            ->paginate(20);

        $productos->setCollection(
            $productos->getCollection()
                ->map(function ($x) {
                    $x->marca_id = $x->marca;
                    $x->marca = $x->productoMarca;
                    unset($x->productoMarca);

                    $x->image = null;

                    if ($x->imagenes->isNotEmpty()) {
                        $x->image = url($x->imagenes->first()->image);
                    } else {
                        $imagen = $x->imagenes()->first();

                        if ($imagen != null) {
                            $x->image = url($imagen->image);
                        }
                    }

                    unset($x->imagenes);
                    return $x;
                })
        );

        return response()->json([
            'productos' => $productos,
            'response' => 'success',
            'status' => 200,
        ], 200);
    }

    public function marcas()
    {
        $marcas = Marca::query()
            ->orderBy('nombre_marca', 'asc')
            ->get();

        return response()->json([
            'response' => 'success',
            'status' => 200,
            'marcas' => $marcas,
        ], 200);
    }

    private function saveImage($image, $name, $id_catalogo, $referencia)
    {
        $extension = array_reverse(explode(".", $image->getClientOriginalName()))[0];
        $filecontent = file_get_contents($image->getRealPath());
        $filename = $name . '.' . $extension;
        $path = public_path("storage/productos/{$id_catalogo}");

        if (!is_dir($path)) {
            mkdir($path);
            if (!is_dir("{$path}/{$referencia}")) {
                mkdir("{$path}/{$referencia}");
            }
        }

        \Storage::disk('productos')->put("/{$id_catalogo}/{$referencia}/$filename", $filecontent);
        return $filename;
    }
}
