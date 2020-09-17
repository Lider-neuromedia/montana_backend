<?php

namespace App\Http\Controllers;

use App\Entities\Producto;
use Illuminate\Http\Request;

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
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $producto = Producto::create($request->all());
        return $producto;
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
