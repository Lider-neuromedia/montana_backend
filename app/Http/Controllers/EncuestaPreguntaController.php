<?php

namespace App\Http\Controllers;

use App\Entities\EncuestaPregunta;
use Illuminate\Http\Request;

class EncuestaPreguntaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return EncuestaPregunta::all();
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
        $ep = EncuestaPregunta::create($request->all());
        return $ep;
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Entities\EncuestaPregunta  $encuestaPregunta
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $ep = EncuestaPregunta::find($id);
        return $ep;
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Entities\EncuestaPregunta  $encuestaPregunta
     * @return \Illuminate\Http\Response
     */
    public function edit(EncuestaPregunta $encuestaPregunta)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Entities\EncuestaPregunta  $encuestaPregunta
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, EncuestaPregunta $encuestaPregunta)
    {
        $encuestaPregunta->update($request->all());
        return $encuestaPregunta;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Entities\EncuestaPregunta  $encuestaPregunta
     * @return \Illuminate\Http\Response
     */
    public function destroy(EncuestaPregunta $encuestaPregunta)
    {
        $encuestaPregunta->delete();
        return $encuestaPregunta;
    }
}
