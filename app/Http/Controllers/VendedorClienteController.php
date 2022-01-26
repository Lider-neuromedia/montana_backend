<?php

namespace App\Http\Controllers;

use App\Entities\User;
use App\Entities\VendedorCliente;
use Illuminate\Http\Request;

class VendedorClienteController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return VendedorCliente::all();
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
        $request->validate([
            'vendedor_id' => 'required',
            'cliente_id' => 'required',
        ]);

        $validate = VendedorCliente::query()
            ->where('vendedor', $request['vendedor_id'])
            ->where('cliente', $request['cliente_id'])
            ->exists();

        if ($validate) {
            return response()->json([
                'response' => 'error',
                'message' => 'La asignación que desea hacer ya se encuentra registrada.',
                'status' => 403,
            ], 403);
        }

        $vendedor_cliente = new VendedorCliente();
        $vendedor_cliente->cliente = $request['cliente_id'];
        $vendedor_cliente->vendedor = $request['vendedor_id'];
        $vendedor_cliente->save();

        return response()->json([
            'response' => 'success',
            'message' => 'Cliente asignado correctamente.',
            'satatus' => 200,
        ], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Entities\VendedorCliente  $vendedorCliente
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $cliente = User::findOrFail($id)
            ->vendedor_clientes()
            ->get();
        return $cliente;
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Entities\VendedorCliente  $vendedorCliente
     * @return \Illuminate\Http\Response
     */
    public function edit(VendedorCliente $vendedorCliente)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Entities\VendedorCliente  $vendedorCliente
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, VendedorCliente $vendedorCliente)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Entities\VendedorCliente  $vendedorCliente
     * @return \Illuminate\Http\Response
     */
    public function destroy(VendedorCliente $vendedorCliente)
    {
        //
    }

    /**
     * Obtener el vendedor asignado al cliente logueado.
     */
    public function vendedorAsignado()
    {
        $cliente = auth()->user();
        $resultado = \DB::table('vendedor_cliente')
            ->where('cliente', $cliente->id)
            ->first();

        if ($resultado == null) {
            return abort(404);
        }

        $vendedor = User::findOrFail($resultado->vendedor);

        return response()->json($vendedor, 200);
    }
}
