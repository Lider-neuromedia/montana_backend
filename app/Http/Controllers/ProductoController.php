<?php

namespace App\Http\Controllers;

use App\Entities\Catalogo;
use App\Entities\GaleriaProducto;
use App\Entities\Producto;
use DB;
use Illuminate\Http\Request;

class ProductoController extends Controller
{
    public function index($catalogo)
    {
        $cat = Catalogo::findOrFail($catalogo);
        $show_room = $cat->tipo == 'show room';

        $productos = Producto::query()
            ->where('catalogo', $catalogo)
            ->whereNull('deleted_at')
            ->get()
            ->map(function ($producto) {
                $marca = \DB::table('marcas')
                    ->where('id_marca', $producto->marca)
                    ->first();

                $imagen = \DB::table('galeria_productos')
                    ->where('producto', $producto->id_producto)
                    ->where('destacada', 1)
                    ->first();

                $producto->nombre_marca = $marca == null ? null : $marca->nombre_marca;
                $producto->image = $imagen == null ? null : url($imagen->image);

                return $producto;
            });

        if ($productos->count() == 0) {
            return response()->json([
                'response' => 'error',
                'status' => 404,
                'message' => 'El catalogo no tiene productos registrados.',
            ], 404);
        }

        return response()->json([
            'response' => 'success',
            'message' => '',
            'status' => 200,
            'productos' => $productos,
            'show_room' => $show_room,
        ], 200);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => ['required'],
            'codigo' => ['required'],
            'referencia' => ['required'],
            'stock' => ['required', 'numeric'],
            'marca' => ['required', 'exists:marcas,id_marca'],
            'descripcion' => ['required'],
            'precio' => ['required', 'numeric'],
            'catalogo' => ['required'],
            'imagenes' => ['required', 'array', 'min:1'],
            'imagenes.*.image' => ['required', 'file', 'max:2000'],
            'imagenes.*.destacada' => ['in:0,1'],
        ]);

        $producto = new Producto();
        $producto->nombre = $request['nombre'];
        $producto->codigo = $request['codigo'];
        $producto->referencia = $request['referencia'];
        $producto->stock = $request['stock'];
        $producto->precio = $request['precio'];
        $producto->descripcion = $request['descripcion'];
        $producto->precio = $request['precio'];
        $producto->total = $request['precio'];
        $producto->descuento = 0;
        $producto->iva = 19;
        $producto->catalogo = $request['catalogo'];
        $producto->marca = $request['marca'];

        $catalogo = Catalogo::findOrFail($request['catalogo']);

        if ($producto->save()) {
            // Update cantidad catalogo.
            $catalogo->cantidad++;
            $catalogo->save();
            // Save images.
            foreach ($request->get('imagenes') as $index => $imagen) {
                $name = "{$producto->referencia}-{$index}";
                $filename = $this->saveImage($request->file('imagenes')[$index]['image'], $name, $producto->catalogo, $producto->referencia);
                $galeria = new GaleriaProducto();
                $galeria->image = "storage/productos/{$producto->catalogo}/{$producto->referencia}/{$filename}";
                $galeria->name_img = $name;
                $galeria->destacada = $imagen['destacada'];
                $galeria->producto = $producto->id_producto;
                $galeria->save();
            }
        }

        return response()->json([
            'response' => 'success',
            'status' => 200,
        ], 200);
    }

    public function saveImage($image, $name, $id_catalogo, $referencia)
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

    public function detalleProducto($id)
    {
        Producto::findOrFail($id);

        $producto = Producto::where('id_producto', $id)
            ->join('marcas', 'marca', '=', 'id_marca')
            ->first();

        $imagenes = GaleriaProducto::where('producto', $id)->get();

        foreach ($imagenes as $imagen) {
            if ($imagen->destacada) {
                $producto->destacada = url($imagen->image);
            }
            $imagen->image = url($imagen->image);
        }

        $producto->imagenes = $imagenes;

        return response()->json([
            'response' => 'success',
            'status' => 200,
            'producto' => $producto,
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'id_producto' => ['required', 'exists:productos,id_producto'],
            'nombre' => ['required'],
            'codigo' => ['required'],
            'referencia' => ['required'],
            'stock' => ['required', 'numeric'],
            'marca' => ['required', 'exists:marcas,id_marca'],
            'descripcion' => ['required'],
            'precio' => ['required', 'numeric'],
            'catalogo' => ['required'],
            'imagenes' => ['nullable', 'array', 'min:1'],
            'imagenes.*.image' => ['nullable', 'file', 'max:2000'],
            'imagenes.*.destacada' => ['in:0,1'],
            'imagenes.*.id_galeria_prod' => ['nullable', 'exists:galeria_productos,id_galeria_prod'],
            'imagenes.*.delete' => ['nullable', 'integer', 'in:0,1'],
        ]);

        try {

            $producto = Producto::findOrFail($request['id_producto']);
            $producto->nombre = $request['nombre'];
            $producto->codigo = $request['codigo'];
            $producto->referencia = $request['referencia'];
            $producto->stock = $request['stock'];
            $producto->marca = $request['marca'];
            $producto->precio = $request['precio'];
            $producto->total = $request['precio'];
            $producto->descripcion = $request['descripcion'];

            // Update images.
            if ($request->has('imagenes') && $request->get('imagenes')) {
                foreach ($request->get('imagenes') as $index => $image) {
                    $es_borrar_imagen = isset($image['delete']) && $image['delete'] == 1;

                    // Borrar imagen.
                    if (isset($image['id_galeria_prod']) && $es_borrar_imagen) {
                        $image_store = GaleriaProducto::find($image['id_galeria_prod']);

                        if ($image_store) {
                            $path_image = public_path($image_store->image);

                            if (file_exists($path_image)) {
                                unlink($path_image);
                            }

                            $image_store->delete();
                        }
                    }

                    // Guardar imagen
                    if (isset($request->file('imagenes')[$index]) && isset($request->file('imagenes')[$index]['image'])) {
                        $imagen = $request->file('imagenes')[$index]['image'];

                        if ($imagen != null) {
                            if (isset($image['id_galeria_prod'])) {

                                // Actualizar imagen.
                                $image_store = GaleriaProducto::find($image['id_galeria_prod']);

                                if ($image_store) {
                                    $name = $image_store->name_img;
                                    $path_image = public_path($image_store->image); // Elimina la imagen anterior.

                                    if (file_exists($path_image)) {
                                        unlink($path_image);
                                    }

                                    // Guardamos la imagen nueva.
                                    $filename = $this->saveImage($imagen, $name, $producto->catalogo, $producto->referencia);
                                    $image_store->image = "storage/productos/{$producto->catalogo}/{$producto->referencia}/{$filename}";
                                    $image_store->destacada = $image['destacada'];
                                    $image_store->save();
                                }

                            } else {

                                $name = "{$producto->referencia}-{$index}";
                                $filename = $this->saveImage($imagen, $name, $producto->catalogo, $producto->referencia);
                                $image_store = new GaleriaProducto();
                                $image_store->name_img = $name;
                                $image_store->producto = $producto->id_producto;
                                $image_store->image = "storage/productos/{$producto->catalogo}/{$producto->referencia}/{$filename}";
                                $image_store->destacada = $image['destacada'];
                                $image_store->save();

                            }
                        }
                    } else {
                        // Actualizar imagen sin archivo nuevo.
                        if (isset($image['id_galeria_prod']) && !$es_borrar_imagen) {
                            $image_store = GaleriaProducto::find($image['id_galeria_prod']);

                            if ($image_store) {
                                $image_store->destacada = $image['destacada'];
                                $image_store->save();
                            }
                        }
                    }
                }
            }

            $producto->save();

            return response()->json([
                'response' => 'success',
                'status' => 200,
                'message' => "Producto actualizado correctamente.",
            ], 200);

        } catch (\Exception $ex) {
            \Log::info($ex->getMessage());
            \Log::info($ex->getTraceAsString());

            return response()->json([
                'response' => 'error',
                'status' => 403,
                'message' => $ex->getMessage(),
            ], 403);
        }
    }

    public function deleteImages($id_producto)
    {
        try {

            $info_product = Producto::findOrFail($id_producto);
            $images_prod = GaleriaProducto::where('producto', $id_producto)->get();

            foreach ($images_prod as $image) {
                $path = public_path($image->image);
                unlink($path);
            }

            rmdir(public_path("storage/productos/{$info_product->catalogo}/{$info_product->referencia}"));
            return true;

        } catch (\Exception $ex) {
            \Log::info($ex->getMessage());
            \Log::info($ex->getTraceAsString());
            return false;
        }
    }

    public function destroy($id)
    {
        $producto = Producto::findOrFail($id);
        $catalogo = Catalogo::find($producto->catalogo);

        if ($catalogo) {
            $catalogo->cantidad--;
            $catalogo->save();
        }

        $producto->delete();

        return response()->json([
            'response' => 'success',
            'status' => 200,
            'message' => "Producto eliminado.",
        ], 200);
    }

    public function getProductsShowRoom()
    {
        $validate_catalogo = Catalogo::where('tipo', 'show room')->where('estado', 'activo');

        if ($validate_catalogo->exists()) {

            $catalogo = $validate_catalogo->first();
            $productos = Producto::where('catalogo', $catalogo->id_catalogo)
                ->join('galeria_productos', 'id_producto', '=', 'producto')
                ->join('marcas', 'marca', '=', 'id_marca')
                ->where('destacada', 1)
                ->get();

            foreach ($productos as $producto) {
                $producto->image = url($producto->image);
            }

            return response()->json([
                'response' => 'success',
                'status' => 200,
                'productos' => $productos,
            ], 200);

        }

        return response()->json([
            'response' => 'error',
            'status' => 403,
            'message' => 'No existe catalogo show room disponible.',
        ], 403);
    }

    public function getMarcas()
    {
        $marcas = DB::table('marcas')->orderBy('nombre_marca', 'asc')->get();

        return response()->json([
            'response' => 'success',
            'status' => 200,
            'marcas' => $marcas,
        ], 200);
    }
}
