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
    public function index(Request $request){
        
        $catalogos = Catalogo::select('*');

        if (isset($request['search'])) {
            $search = json_decode($request['search'], true);

            if ($search['general']) {
                $catalogos->where('tipo', 'general');
            }
            if($search['show_room']){
                $catalogos->orWhere('tipo', 'show room');
            }
            if($search['public']){
                $catalogos->where('estado', 'activo');
            } 
            if($search['private']){
                $catalogos->orWhere('estado', 'privado');
            }

            $catalogos = $catalogos->get();
            
        }else{
            $catalogos = Catalogo::all();
        }
        

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
            'tipo' => 'required',
            'image' => 'required',
        ]);

        $catalogo = new Catalogo();
        $catalogo->titulo = $request['nombre'];

        if ($request['tipo'] == 'show room' && $request['estado'] == 'activo') {
            $validate_show_room = Catalogo::where('tipo', 'show room')->where('estado', 'activo')->exists();
            if (!$validate_show_room) {
                // Si no existe un catalogo show room activo sigue normal.
                $catalogo->estado = $request['estado'];
            }else{
                // Si existe. No se crea el catalogo y se devuelve al front.
                $response = [
                    'response' => 'warning',
                    'status' => 200,
                    'message' => 'Solo debe existir un catalogo show room activo. Por favor inactive el anterior catalogo antes de activar uno nuevo.'
                ];
                
                return response()->json($response);
            }   
        }else{
            $catalogo->estado = $request['estado'];
        }

        $catalogo->tipo = $request['tipo'];
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
    public function update(Request $request){
        $request->validate([
            'id_catalogo' => 'required',
            'titulo' => 'required',
            'estado' => 'required',
            'tipo' => 'required',
            'imagen' => 'required',
        ]);
        $validate_image = $this->validateLinkImage($request['imagen']);

        $catalogo = Catalogo::find($request['id_catalogo']);
        $catalogo->titulo = $request['titulo'];
        
        if ($request['tipo'] == 'show room' && $request['estado'] == 'activo') {
            $validate_show_room = Catalogo::where('tipo', 'show room')->where('estado', 'activo');
            if (!$validate_show_room->exists()) {
                // Si no existe un catalogo show room activo sigue normal.
                $catalogo->estado = $request['estado'];
            }else{
                // Validar que el catalogo no sea el mismo.
                if ($validate_show_room->first()->id_catalogo == $request['id_catalogo']) {
                    $catalogo->estado = $request['estado'];
                }else{
                    // Si existe. Actualiza lo demas y retorna una advertencia.
                    $catalogo->tipo = $request['tipo'];
                    $catalogo->descuento = (isset($request['descuento'])) ? $request['descuento'] : $catalogo->descuento;
                    $catalogo->save();
    
                    $response = [
                        'response' => 'warning',
                        'status' => 200,
                        'message' => 'Solo debe existir un catalogo show room activo. Por favor inactive el anterior catalogo antes de activar uno nuevo.'
                    ];
                    
                    return response()->json($response);
                }
            }   

        }else{
            $catalogo->estado = $request['estado'];
        }
        
        $catalogo->tipo = $request['tipo'];
        $catalogo->descuento = (isset($request['descuento'])) ? $request['descuento'] : $catalogo->descuento;
        $catalogo->save();
        if ($validate_image) {
            $filename = $this->saveImage($request['imagen'], $catalogo->id_catalogo);
            $catalogo->imagen = "storage/catalogos/{$filename}";
            $catalogo->save();
        }

        return response()->json(['response' => 'success', 'status' => 200]);
    }
    
    public function validateLinkImage($image){
        $substr_image = substr($image, 0, 4);
        if ($substr_image == 'http') {
            return false;
        }else{
            return true;
        }
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

    public function consumerCatalogos(){
        $catalogos = Catalogo::where('estado', 'activo')->where('cantidad', '!=', 0)->get();
        foreach ($catalogos as $catalogo) {
            $catalogo->imagen = url($catalogo->imagen);
        }
        $response = [
            'response' => 'success',
            'status' => 200,
            'catalogos' => $catalogos
        ];

        return response()->json($response);
    }
}
