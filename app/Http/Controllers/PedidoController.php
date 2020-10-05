<?php

namespace App\Http\Controllers;

use App\Entities\Catalogo;
use App\Entities\Pedido;
use App\Entities\PedidoProduct;
use App\Entities\Producto;
use App\Entities\User;
use Illuminate\Http\Request;
use DB;

class PedidoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(){
        $pedidos = Pedido::select('fecha', 'codigo', 'total', 'ven.name AS name_vendedor', 
                    'ven.apellidos AS apellido_vendedor', 'cli.name AS name_cliente', 'cli.apellidos AS apellido_cliente', 'estado')
                    ->join('users AS ven', 'vendedor', '=','ven.id')
                    ->join('users AS cli', 'cliente', '=','cli.id')
                    ->get();

        $response = [
            'response' => 'success',
            'status' => 200,
            'pedidos' => $pedidos 
        ];

        return response()->json($response);
    }

    public function resourcesCreate(){
        $vendedores = User::where('rol_id', 2)->get();
        $clientes = User::where('rol_id', 3)->get();
        $catalogos = Catalogo::where('estado', 'activo')->where('cantidad', '!=', 0)->get();

        $response = [
            'response' => 'success',
            'status' => 200,
            'vendedores' => $vendedores, 
            'clientes' => $clientes, 
            'catalogos' => $catalogos
        ];

        return response()->json($response);
    }


    public function tiendaCliente($id){
        $tiendas = DB::table('tiendas')->where('cliente', $id)->get();
        return response()->json($tiendas);
    }

    public function generateCodePedido(){
        $code = uniqid();
        $validate_code = Pedido::where('codigo', $code)->exists();
        if (!$validate_code) {
            $response = [
                'response' => 'success',
                'status' => 200,
                'code' => $code
            ];
        }else{
            $response = [
                'response' => 'error',
                'status' => 403,
                'message' => 'El codigo esta registrado. Intenta de nuevo.'
            ];
        }
        return response()->json($response);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request){
        $request->validate([
            'cliente' => 'required',
            'vendedor' => 'required',
            'catalogo' => 'required',
            'codigo_pedido' => 'required',
            'productos' => 'required',
            'total_pedido' => 'required|numeric',
            'descuento' => 'required|numeric',
            'forma_pago' => 'required',
            'notas' => 'required',
        ]);

        $pedido = new Pedido();
        $pedido->fecha = date('Y-m-d');
        $pedido->codigo = $request['codigo_pedido'];
        $pedido->metodo_pago = $request['forma_pago'];
        $pedido->sub_total = $request['total_pedido'];
        $pedido->descuento = $request['descuento'];
        // Sacamos el descuento si lo tiene.
        if ($request['descuento'] != 0) {
            $descuento = $request['total_pedido'] * ($request['descuento'] / 100);
            $total = $request['total_pedido'] - $descuento;
        }else{
            $total = $request['total_pedido'];
        }

        $pedido->total = $total;
        $pedido->notas = $request['notas'];
        $pedido->vendedor = $request['vendedor'];
        $pedido->cliente = $request['cliente'];
        $pedido->estado = 2;
        // Creamos la relacion con productos.
        if ($pedido->save()) {
            foreach ($request['productos'] as $product) {
                $pedido_product = new PedidoProduct();
                $pedido_product->pedido = $pedido->id_pedido;
                $pedido_product->producto = $product['id_producto'];
                if($pedido_product->save()){
                    $cantidad = 0;
                    foreach ($product['tiendas'] as $tienda) {
                        $cantidad += $tienda['cantidad'];
                    }
                    $producto = Producto::find($product['id_producto']);
                    $producto->stock = $producto->stock - $cantidad;
                    $producto->save();
                } 
            }
        }

        $response = [
            'status' => 200,
            'response' => 'success',
            'message' => 'Pedido creado.'
        ];

        return response()->json($response);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Entities\Pedido  $pedido
     * @return \Illuminate\Http\Response
     */
    public function show(Pedido $pedido)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Entities\Pedido  $pedido
     * @return \Illuminate\Http\Response
     */
    public function edit(Pedido $pedido)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Entities\Pedido  $pedido
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Pedido $pedido)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Entities\Pedido  $pedido
     * @return \Illuminate\Http\Response
     */
    public function destroy(Pedido $pedido)
    {
        //
    }
}
