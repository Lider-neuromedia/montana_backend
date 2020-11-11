<?php

namespace App\Http\Controllers;

use App\Entities\Encuesta;
use App\Entities\Preguntas;
use App\Entities\Valoracion;
use App\Entities\Catalogo;
use Illuminate\Http\Request;
use DB;

class EncuestaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request){
        $encuestas = Encuesta::select('encuestas.*', 'catalogos.titulo')
                    ->join('catalogos', 'catalogo', '=', 'id_catalogo')
                    ->get();

        $response = [
            'response' => 'success',
            'status' => 200,
            'encuestas' => $encuestas
        ];

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
            'catalogo' => 'exists:App\Entities\Catalogo,id_catalogo|required',
            'tipo' => 'required',
            'preguntas' => 'required|array|max:5'
        ]);
        
        // Validar que el catalogo no tenga encuesta registrada y activa.
        $validate_catalogo = Encuesta::where('catalogo', $request['catalogo'])->where('estado', 'activo')->exists();

        if($validate_catalogo){
            $response = [
                'response' => 'error',
                'status' => 403,
                'message' => 'El catalogo seleccionado ya tiene encuesta activa. Por favor inactive la encuesta para poder crear una nueva'
            ];
            return response()->json($response);
        }else{
            $encuesta = new Encuesta;
            $encuesta->codigo = uniqid();
            $encuesta->fecha_creacion = date('Y-m-d');
            $encuesta->catalogo = $request['catalogo'];
            $encuesta->tipo = $request['tipo'];
            $encuesta->estado = 'activo';
            
            if($encuesta->save()){
                foreach ($request['preguntas'] as $pregunta) {
                    $pregunta_db = new Preguntas;
                    $pregunta_db->encuesta = $encuesta->id_form;
                    $pregunta_db->pregunta = $pregunta['name'];
                    $pregunta_db->save();
                }
                $response = [
                    'response' => 'success',
                    'status' => 200,
                ];
            }else{
                $response = [
                    'response' => 'error',
                    'status' => 403,
                    'message' => 'Error en la creación de la encuesta.'
                ];
            }
        }

        return response()->json($response);
    }

    /**
     * Obtener las preguntas activas del catalogo en cuestion.
     *
     * @param  \App\Entities\Catalogo  $catalogo
     * @return \Illuminate\Http\Response
     */
    public function getPreguntas($catalogo){
        $validate_catalogo = Catalogo::find($catalogo);
        if ($validate_catalogo) {
            $usuario = auth()->user();
            $encuesta_preguntas = Encuesta::select('id_form','catalogo','preguntas.*')
                                    ->where('catalogo', $catalogo)
                                    ->where('estado', 'activo')
                                    ->join('preguntas', 'encuesta', '=', 'id_form')
                                    ->get();
            
            $validate_user = Encuesta::select('id_form','catalogo','preguntas.*')
                            ->join('preguntas', 'encuesta', '=', 'id_form')
                            ->join('valoraciones', 'valoraciones.pregunta', '=', 'id_pregunta')
                            ->where('catalogo', $catalogo)
                            ->where('estado', 'activo')
                            ->where('usuario', $usuario->id)
                            ->exists();

            foreach ($encuesta_preguntas as $pregunta) {
                $pregunta->respuesta = 0;
            }
            $response = [
                'response' => 'success',
                'status' => 200,
                'preguntas' => $encuesta_preguntas,
                'respuesta_usuario' => $validate_user 
            ];
        }else{
            $response = [
                'response' => 'error',
                'status' => 403,
                'preguntas' => 'Catalogo no existe en base de datos.'
            ];
        }

        return response()->json($response);

    }

    /**
     * Crear las valoraciones de los clientes o vendedores segun el producto.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\Response
     */
    public function storePreguntas(Request $request){
        $request->validate([
            'usuario' => 'exists:App\Entities\User,id|required',
            'producto' => 'exists:App\Entities\Producto,id_producto|required',
            'preguntas' => 'required|array|min:1',
            'preguntas.*.respuesta' => 'required|max:5',
            'preguntas.*.pregunta' => 'exists:App\Entities\Preguntas,id_pregunta|required',
        ]);

        foreach ($request['preguntas'] as $pregunta) {
            $respuestas = new Valoracion;
            $respuestas->pregunta = $pregunta['pregunta'];
            $respuestas->usuario = $request['usuario'];
            $respuestas->producto = $request['producto'];
            $respuestas->respuesta = $pregunta['respuesta'];
            $respuestas->save();
        }

            $response = [
                'response' => 'success',
                'status' => 200,
            ];

        return response()->json($response);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Entities\Encuesta  $encuesta
     * @return \Illuminate\Http\Response
     */
    public function show($id){
        $preguntas = DB::table('preguntas')
                    ->select('id_pregunta', 'preguntas.pregunta', DB::raw('COUNT(id_pregunta) AS cant_respuestas, round(avg(respuesta)) AS promedio'))
                    ->join('valoraciones', 'valoraciones.pregunta', '=', 'id_pregunta')
                    ->where('encuesta', $id)
                    ->groupBy('id_pregunta', 'preguntas.pregunta')
                    ->get();
        
        $response = [
            'response' => 'success',
            'status' => 200,
            'preguntas' => $preguntas
        ];
        
        return response()->json($response);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Entities\Encuesta  $encuesta
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Encuesta $encuesta)
    {
        $encuesta->update($request->all());
        return $encuesta;
    }

}
