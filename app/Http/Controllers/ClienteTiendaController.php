<?php

namespace App\Http\Controllers;

use App\Entities\ClienteTienda;
use Illuminate\Http\Request;

class ClienteTiendaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return ClienteTienda::all();
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
        $ct = ClienteTienda::create($request->all());
        return $ct;
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Entities\ClienteTienda  $clienteTienda
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $ct = Ciudad::find($id);
        return $ct;
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Entities\ClienteTienda  $clienteTienda
     * @return \Illuminate\Http\Response
     */
    public function edit(ClienteTienda $clienteTienda)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Entities\ClienteTienda  $clienteTienda
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ClienteTienda $clienteTienda)
    {
        $clienteTienda->update($request->all());
        return $clienteTienda;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Entities\ClienteTienda  $clienteTienda
     * @return \Illuminate\Http\Response
     */
    public function destroy(ClienteTienda $clienteTienda)
    {
        $clienteTienda->delete();
        return $clienteTienda;
    }
}
