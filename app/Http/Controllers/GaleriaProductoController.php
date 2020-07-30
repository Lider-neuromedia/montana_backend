<?php

namespace App\Http\Controllers;

use App\Entities\GaleriaProducto;
use Illuminate\Http\Request;

class GaleriaProductoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return GaleriaProducto::all();
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
        $gp = GaleriaProducto::create($request->all());
        return $gp;
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Entities\GaleriaProducto  $galeriaProducto
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $gp = GaleriaProducto::find($id);
        return $gp;
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Entities\GaleriaProducto  $galeriaProducto
     * @return \Illuminate\Http\Response
     */
    public function edit(GaleriaProducto $galeriaProducto)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Entities\GaleriaProducto  $galeriaProducto
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, GaleriaProducto $galeriaProducto)
    {
        $galeriaProducto->update($request->all());
        return $galeriaProducto;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Entities\GaleriaProducto  $galeriaProducto
     * @return \Illuminate\Http\Response
     */
    public function destroy(GaleriaProducto $galeriaProducto)
    {
        $galeriaProducto->delete();
        return $galeriaProducto;
    }
}
