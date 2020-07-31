<?php

namespace App\Http\Controllers;

use App\Entities\VendedorCliente;
use App\Entities\User;
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
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Entities\VendedorCliente  $vendedorCliente
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $cliente = User::find($id)->vendedor_clientes()->get();
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
}
