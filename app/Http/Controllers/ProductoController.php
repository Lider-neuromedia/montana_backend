<?php

namespace App\Http\Controllers;

use App\Entities\Catalogo;
use App\Entities\GaleriaProducto;
use App\Entities\Producto;
use DB;
use Illuminate\Http\Request;

class ProductoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($catalogo)
    {
        $productos = Producto::select('productos.*', 'image', 'nombre_marca')
            ->join('galeria_productos', 'id_producto', '=', 'producto')
            ->join('marcas', 'marca', '=', 'id_marca')
            ->where('catalogo', $catalogo)
            ->where('destacada', 1)
            ->get();

        $catalogo = Catalogo::findOrFail($catalogo);
        if ($catalogo->tipo == 'show room') {
            $show_room = true;
        } else {
            $show_room = false;
        }

        if (count($productos) == 0) {
            return response()->json(['response' => 'error', 'status' => 404, 'message' => 'El catalogo no tiene productos registrados.']);
        } else {

            foreach ($productos as $producto) {
                $producto->image = url($producto->image);
            }

            $response = [
                'response' => 'success',
                'message' => '',
                'status' => 200,
                'productos' => $productos,
                'show_room' => $show_room,
            ];

            return response()->json($response);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required',
            'codigo' => 'required',
            'referencia' => 'required',
            'stock' => 'required|numeric',
            'marca' => 'required',
            'descripcion' => 'required',
            'precio' => 'required|numeric',
            'catalogo' => 'required',
            'imagenes' => 'required|array|min:1',
            'imagenes.*.image' => 'required|image',
            'imagenes.*.destacada' => 'in:0,1',
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

        $response = ['response' => 'success', 'status' => 200];
        return response()->json($response);
    }

    // Store image.
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
        $response = [
            'response' => 'success',
            'status' => 200,
            'producto' => $producto,
        ];
        return response()->json($response);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Entities\Producto  $producto
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'id_producto' => 'required|exists:productos,id_producto',
            'nombre' => 'required',
            'codigo' => 'required',
            'referencia' => 'required',
            'stock' => 'required|numeric',
            'marca' => 'required',
            'descripcion' => 'required',
            'precio' => 'required|numeric',
            'catalogo' => 'required',
            'imagenes' => 'nullable|array|min:1',
            'imagenes.*.image' => 'nullable|image',
            'imagenes.*.destacada' => 'in:0,1',
            'imagenes.*.id_galeria_prod' => 'nullable|exists:galeria_productos,id_galeria_prod',
        ]);

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
                if (isset($request->file('imagenes')[$index]) && isset($request->file('imagenes')[$index]['image'])) {
                    $imagen = $request->file('imagenes')[$index]['image'];

                    if ($imagen != null) {
                        if (isset($image['id_galeria_prod'])) {

                            $image_store = GaleriaProducto::find($image['id_galeria_prod']);
                            $name = $image_store->name_img;
                            // Elimina la imagen anterior.
                            $path_image = public_path($image_store->image);
                            if (file_exists($path_image)) {
                                unlink($path_image);
                            }
                            // Guardamos la imagen nueva.
                            $filename = $this->saveImage($imagen, $name, $producto->catalogo, $producto->referencia);
                            $image_store->image = "storage/productos/{$producto->catalogo}/{$producto->referencia}/{$filename}";
                            $image_store->destacada = $image['destacada'];
                            $image_store->save();

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
                }
            }
        }

        try {
            $producto->save();
            $response = [
                'response' => 'success',
                'status' => 200,
                'message' => "Producto actualizado correctamente.",
            ];
        } catch (\Exception $e) {
            $response = [
                'response' => 'error',
                'status' => 403,
                'message' => $e->getMessage(),
            ];
        }

        return response()->json($response);
    }

    public function deleteImages($id_producto)
    {
        $info_product = Producto::find($id_producto);
        $images_prod = GaleriaProducto::where('producto', $id_producto)->get();
        foreach ($images_prod as $image) {
            $path = public_path($image->image);
            unlink($path);
        }
        try {
            rmdir(public_path("storage/productos/{$info_product->catalogo}/{$info_product->referencia}"));
        } catch (\Exception $e) {
            return false;
        }

        return true;
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
        ]);
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

            $response = [
                'response' => 'success',
                'status' => 200,
                'productos' => $productos,
            ];
        } else {
            $response = [
                'response' => 'error',
                'status' => 403,
                'message' => 'No existe catalogo show room disponible.',
            ];
        }

        return response()->json($response);
    }

    public function getMarcas()
    {
        $marcas = DB::table('marcas')->get();

        $response = [
            'response' => 'success',
            'status' => 200,
            'marcas' => $marcas,
        ];

        return response()->json($response);
    }

}
