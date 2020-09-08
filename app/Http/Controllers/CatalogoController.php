<?php

namespace App\Http\Controllers;

use App\Entities\Catalogo;
use Illuminate\Http\Request;

class CatalogoController extends Controller
{
    /**
     * Listado de todos los catalogos.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(){
        $catalogos = Catalogo::all();

        // Setear la url de la imagen segun servidor.
        foreach ($catalogos as $catalogo) {
            $catalogo->imagen = url($catalogo->imagen);
        }

        return $catalogos;
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
     * Crear un nuevo catalogo.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $catalogo = Catalogo::  create($request->all());
        return $catalogo;
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Entities\Catalogo  $catalogo
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $catalogo = Catalogo::find($id);
        return $catalogo;
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Entities\Catalogo  $catalogo
     * @return \Illuminate\Http\Response
     */
    public function edit(Catalogo $catalogo)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Entities\Catalogo  $catalogo
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Catalogo $catalogo)
    {
        $catalogo->update($request->all());
        return $catalogo;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Entities\Catalogo  $catalogo
     * @return \Illuminate\Http\Response
     */
    public function destroy(Catalogo $catalogo)
    {
        $catalogo->delete();
        return $catalogo;
    }
}
