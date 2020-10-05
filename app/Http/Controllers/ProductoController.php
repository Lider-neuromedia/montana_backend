<?php

namespace App\Http\Controllers;

use App\Entities\Catalogo;
use App\Entities\Producto;
use Illuminate\Http\Request;
use App\Entities\GaleriaProducto;
use DB;

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
        $producto->total = $request['precio'];
        $producto->descuento = 0;
        $producto->iva = 19;
        $producto->catalogo = $request['catalogo'];
        $producto->marca = $request['marca'];
        
        if ($producto->save()) {
            // Update cantidad catalogo.
            $catalogo = Catalogo::find($request['catalogo']);
            $catalogo->cantidad++;
            $catalogo->save();
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


    public function detalleProducto($id){
        $producto = Producto::where('id_producto', $id)
                    ->join('marcas', 'marca', '=', 'id_marca')
                    ->first();
        
        $imagenes = GaleriaProducto::where('producto', $id)->get();
        
        foreach ($imagenes as $imagen) {
            if($imagen->destacada){
                $producto->destacada = url($imagen->image);
            }
            $imagen->image = url($imagen->image);
        }

        $producto->imagenes = $imagenes;
        $response = [
            'response' => 'success', 
            'status' => 200,
            'producto' => $producto
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
    public function update(Request $request, $id){
        $request->validate([
            'id_producto' => 'required',
            'nombre' => 'required',
            'codigo' => 'required',
            'referencia' => 'required',
            'stock' => 'required|numeric',
            'marca' => 'required',
            'descripcion' => 'required',
            'precio' => 'required|numeric',
            'catalogo' => 'required',
        ]);
        
        $producto = Producto::find($request['id_producto']);
        $producto->nombre = $request['nombre'];
        $producto->codigo = $request['codigo'];
        $producto->referencia = $request['referencia'];
        $producto->stock = $request['stock'];
        $producto->marca = $request['marca'];
        $producto->precio = $request['precio'];
        $producto->total = $request['precio'];
        $producto->descripcion = $request['descripcion'];
        
        try {
            $producto->save();
            $response = [
                'response' => 'success',
                'status' => 200,
                'message' => "Producto actualizado correctamente."
            ];
        } catch (\Exception $e) {
            $response = [
                'response' => 'error', 
                'status' => 403,
                'message' => $e->getMessage()
            ];
        }

        return response()->json($response);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Entities\Producto  $producto
     * @return \Illuminate\Http\Response
     */
    public function destroy($id){
        
        $validate_producto = DB::table('pedido_productos')->where('producto', $id)->exists();

        if ($validate_producto) {
            $response = [
                'response' => 'error', 
                'status' => 403,
                'message' => "El producto tiene registro de pagos. No es posible que se elimine."
            ];
        }else{
            $delete_images = GaleriaProducto::where('producto', $id)->delete();
            if ($delete_images) {
                $producto = Producto::find($id);

                // Update cantidad catalogo.
                $catalogo = Catalogo::find($producto->catalogo);
                $catalogo->cantidad--;
                if($catalogo->save()){
                    $producto->delete();  
                    $response = [
                        'response' => 'success', 
                        'status' => 200,
                        'message' => "Producto eliminado."
                    ];  
                }else{
                    $response = [
                        'response' => 'error', 
                        'status' => 403,
                        'message' => "No se pudo eliminar el producto."
                    ];  
                }
            }else{
                $response = [
                    'response' => 'error', 
                    'status' => 403,
                    'message' => "No se pudo eliminar el producto."
                ];  
            }
        }
        return response()->json($response);
    }
}
