<?php

namespace App\Http\Controllers;

use App\Entities\Producto;
use Illuminate\Http\Request;
use App\Entities\GaleriaProducto;

class ProductoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($catalogo){
        $productos = Producto::where('catalogo', $catalogo)
                    ->join('galeria_productos', 'id_producto', '=', 'producto')
                    ->join('marcas', 'marca', '=', 'id_marca')
                    ->where('destacada', 1)
                    ->get();
        
        if(count($productos) == 0){
            return response()->json(['response' => 'error', 'status' => 404, 'message' => 'El catalogo no tiene productos registrados.']);
        }else{
            
            foreach ($productos as $producto) {
                $producto->image = url($producto->image);
            }

            $response = [
                'response' => 'success',
                'message' => '',
                'status' => 200,
                'productos' => $productos
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
    public function store(Request $request){
        $request->validate([
            'nombre' => 'required',
            'codigo' => 'required',
            'referencia' => 'required',
            'stock' => 'required|numeric',
            'marca' => 'required',
            'descripcion' => 'required',
            'precio' => 'required|numeric',
            'imagenes' => 'required',
            'catalogo' => 'required',
        ]);
        
        $producto = new Producto();
        $producto->nombre = $request['nombre'];
        $producto->codigo = $request['codigo'];
        $producto->referencia = $request['referencia'];
        $producto->stock = $request['stock'];
        $producto->precio = $request['precio'];
        $producto->descripcion = $request['descripcion'];
        $producto->precio = $request['precio'];
        $producto->descuento = 0;
        $producto->iva = 19;
        $producto->catalogo = $request['catalogo'];
        $producto->marca = $request['marca'];
        
        if ($producto->save()) {
            // Save images.
            foreach ($request['imagenes'] as $key => $imagen) {
                $name = "{$producto->referencia}-{$key}";
                $filename = $this->saveImage($imagen['image'], $name, $producto->catalogo);
                $galeria = new GaleriaProducto();
                $galeria->image = "storage/productos/{$producto->catalogo}/{$filename}";
                $galeria->destacada = $imagen['destacada'];
                $galeria->producto = $producto->id_producto;
                $galeria->save();
            }
        }
        $response = ['response' => 'success', 'status' => 200];
        return response()->json($response);
    }

    // Store image.
    
    public function saveImage($image, $name, $id_catalogo){
        $extension = explode('/', explode(':', substr($image, 0, strpos($image, ';')))[1])[1];   // .jpg .png .pdf
        $replace = substr($image, 0, strpos($image, ',')+1);

        $image = str_replace($replace, '', $image);
        $image = str_replace(' ', '+', $image);
        $filename = $name.'.'.$extension;

        $path = public_path("storage/productos/{$id_catalogo}"); 
        if (!is_dir($path)) {
            mkdir($path);
        }

        \Storage::disk('productos')->put("/{$id_catalogo}/$filename", base64_decode($image));

        return $filename;
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Entities\Producto  $producto
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $producto = Producto::find($id);
        return $producto;
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Entities\Producto  $producto
     * @return \Illuminate\Http\Response
     */
    public function edit(Producto $producto)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Entities\Producto  $producto
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Producto $producto)
    {
        $producto->update($request->all());
        return $producto;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Entities\Producto  $producto
     * @return \Illuminate\Http\Response
     */
    public function destroy(Producto $producto)
    {
        $producto->delete();
        return $producto;
    }
}
