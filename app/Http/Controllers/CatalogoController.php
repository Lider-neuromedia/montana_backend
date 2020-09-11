<?php

namespace App\Http\Controllers;

use App\Entities\Catalogo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use DB;

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

        $response = [
            'response' => 'success',
            'message' => '',
            'status' => 200,
            'catalogos' => $catalogos
        ];

        return response()->json($response);
    }


    /**
     * Crear un nuevo catalogo.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request){
        $request->validate([
            'nombre' => 'required',
            'estado' => 'required',
            'image' => 'required',
        ]);

        $catalogo = new Catalogo();
        $catalogo->titulo = $request['nombre'];
        $catalogo->estado = $request['estado'];
        $catalogo->cantidad = 0;
        $catalogo->save();
        $filename = $this->saveImage($request['image'], $catalogo->id_catalogo);
        $catalogo->imagen = "storage/catalogos/{$filename}";
        $catalogo->save();

        return response()->json(['response' => 'success', 'status' => 200]);
    }

    public function saveImage($image, $id_catalogo){
        $extension = explode('/', explode(':', substr($image, 0, strpos($image, ';')))[1])[1];   // .jpg .png .pdf
        $replace = substr($image, 0, strpos($image, ',')+1); 

        $image = str_replace($replace, '', $image); 
        $image = str_replace(' ', '+', $image); 
        $filename = $id_catalogo.'.'.$extension;
        \Storage::disk('catalogos')->put($filename, base64_decode($image));

        return $filename;
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
    public function destroy($id){
        $catalogo = Catalogo::find($id);
        if ($catalogo->cantidad == 0) {
            $catalogo->delete();
            $response = [
                'response' => 'success',
                'status' => 200
            ];
        }else{
            $response = [
                'response' => 'error',
                'status' => 401,
                'message' => 'El catalogo tiene productos registrados. No se puede eliminar.'
            ];
        }
        return response()->json($response);
    }
}
