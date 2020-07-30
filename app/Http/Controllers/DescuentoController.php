<?php

namespace App\Http\Controllers;

use App\Entities\Descuento;
use Illuminate\Http\Request;

class DescuentoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return Descuento::all();
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
        $descuento = Descuento::create($request->all());
        return $descuento;
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Entities\Descuento  $descuento
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $descuento = Descuento::find($id);
        return $descuento;
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Entities\Descuento  $descuento
     * @return \Illuminate\Http\Response
     */
    public function edit(Descuento $descuento)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Entities\Descuento  $descuento
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Descuento $descuento)
    {
        $descuento->update($request->all());
        return $descuento;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Entities\Descuento  $descuento
     * @return \Illuminate\Http\Response
     */
    public function destroy(Descuento $descuento)
    {
        $descuento->delete();
        return $descuento;
    }
}
