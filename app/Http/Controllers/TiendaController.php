<?php

namespace App\Http\Controllers;

use App\Entities\Tienda;
use Illuminate\Http\Request;
use DB;

class TiendaController extends Controller{
    
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request){
        $request->validate([
            'cliente' => 'required',
            'tiendas' => 'required'
        ]);

        foreach ($request['tiendas'] as $tienda) {
            $result = DB::table('tiendas')->insert([
                'nombre' => $tienda['nombre'],
                'lugar' => $tienda['lugar'],
                'local' => $tienda['local'],
                'direccion' => $tienda['direccion'],                
                'telefono' => $tienda['direccion'],
                'cliente' => $request['cliente']
            ]);
            if (!$result) {
                $response = [
                    'response' => 'success',
                    'status' => 200,
                    'message' => 'error con la data'
                ];
                return response()->json($response);
            }
        }

        $response = [
            'response' => 'success',
            'status' => 200
        ];

        return response()->json($response);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Entities\Tienda  $tienda
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $tienda = Tienda::find($id);
        return $tienda;
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Entities\Tienda  $tienda
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id){
        $request->validate([
            'nombre' => 'required',
            'lugar' => 'required',
            'direccion' => 'required'
        ]);

        $tienda = Tienda::find($id);
        $tienda->nombre = $request['nombre'];
        $tienda->lugar = $request['lugar'];
        $tienda->local = $request['local'];
        $tienda->direccion = $request['direccion'];
        $tienda->telefono = $request['telefono'];

        if ($tienda->save()) {
            $response = [
                'response' => 'success',
                'status' => 200
            ];
        }else{
            $response = [
                'response' => 'success',
                'status' => 200,
                'message' => 'error en la edición.'
            ];
        }

        return response()->json($response);

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Entities\Tienda  $tienda
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request){
        if (isset($request['tiendas'])) {
            foreach ($request['tiendas'] as $tienda) {
                // Validar si la tienda tiene pedidos.
                $pedido = DB::table('pedido_productos')->where('tienda', $tienda)->exists();
                if ($pedido) {
                    $response = [
                        'response' => 'error',
                        'status' => 403,
                        'message' => "La tienda #{$tienda} no se puede eliminar ya que tiene un pedido anclado."
                    ];
                    return response()->json($response);
                }else{

                    $tienda = Tienda::find($tienda);
                    if (!$tienda->delete()) {
                        $response = [
                            'response' => 'error',
                            'status' => 403,
                            'message' => "No se puedele eliminar la tienda #{$tienda}."
                        ];
                        return response()->json($response);
                    }else{
                        $response = [
                            'response' => 'success',
                            'status' => 200,
                            'message' => "Tienda eliminada."
                        ];
                    }
                }
            }

            return response()->json($response);
        }
    }
}
