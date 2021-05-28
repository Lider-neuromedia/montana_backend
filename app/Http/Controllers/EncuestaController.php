<?php

namespace App\Http\Controllers;

use App\Entities\Catalogo;
use App\Entities\Encuesta;
use App\Entities\Preguntas;
use App\Entities\User;
use App\Entities\Valoracion;
use DB;
use Illuminate\Http\Request;

class EncuestaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if (isset($request['search'])) {
            $search = $request['search'];
        } else {
            $search = "";
        }

        $encuestas = Encuesta::select('encuestas.*', 'catalogos.titulo')
            ->join('catalogos', 'catalogo', '=', 'id_catalogo')
            ->where('codigo', 'like', "%$search%")
            ->orWhere('catalogos.titulo', 'like', "%$search%")
            ->orWhere('encuestas.tipo', 'like', "%$search%")
            ->orWhere('encuestas.estado', 'like', "%$search%")
            ->get();

        $response = [
            'response' => 'success',
            'status' => 200,
            'encuestas' => $encuestas,
        ];

        return response()->json($response);
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
            'catalogo' => 'exists:App\Entities\Catalogo,id_catalogo|required',
            'tipo' => 'required',
            'preguntas' => 'required|array|max:5',
        ]);

        // Validar que el catalogo no tenga encuesta registrada y activa.
        $validate_catalogo = Encuesta::where('catalogo', $request['catalogo'])->where('estado', 'activo')->exists();

        if ($validate_catalogo) {
            $response = [
                'response' => 'error',
                'status' => 403,
                'message' => 'El catalogo seleccionado ya tiene encuesta activa. Por favor inactive la encuesta para poder crear una nueva',
            ];
            return response()->json($response);
        } else {
            $encuesta = new Encuesta;
            $encuesta->codigo = uniqid();
            $encuesta->fecha_creacion = date('Y-m-d');
            $encuesta->catalogo = $request['catalogo'];
            $encuesta->tipo = $request['tipo'];
            $encuesta->estado = 'activo';

            if ($encuesta->save()) {
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
            } else {
                $response = [
                    'response' => 'error',
                    'status' => 403,
                    'message' => 'Error en la creación de la encuesta.',
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
    public function getPreguntas($catalogo)
    {
        $validate_catalogo = Catalogo::find($catalogo);
        if ($validate_catalogo) {
            $usuario = auth()->user();
            $encuesta_preguntas = Encuesta::select('id_form', 'catalogo', 'preguntas.*')
                ->where('catalogo', $catalogo)
                ->where('estado', 'activo')
                ->join('preguntas', 'encuesta', '=', 'id_form')
                ->get();

            $validate_user = Encuesta::select('id_form', 'catalogo', 'preguntas.*')
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
                'respuesta_usuario' => $validate_user,
            ];
        } else {
            $response = [
                'response' => 'error',
                'status' => 403,
                'preguntas' => 'Catalogo no existe en base de datos.',
            ];
        }

        return response()->json($response);
    }

    public function getProductoValoraciones($producto)
    {
        $prd = \DB::table('productos')
            ->where('id_producto', $producto)
            ->first();

        $data = \DB::table('valoraciones as v')
            ->select(['p.id_pregunta', 'p.pregunta', 'v.respuesta', 'v.usuario'])
            ->leftJoin('preguntas as p', 'v.pregunta', '=', 'p.id_pregunta')
            ->leftJoin('encuestas as e', 'p.encuesta', '=', 'e.id_form')
            ->where('v.producto', $producto)
            ->where('e.catalogo', $prd->catalogo)
            ->orderBy('p.pregunta')
            ->orderBy('v.usuario')
            ->get();

        $data_usuarios = $data->map(function ($registro) {
            return $registro->usuario;
        })->unique()->flatten();

        $valoraciones = [];

        foreach ($data as $item) {
            $item->respuesta = $item->respuesta == 0 ? 1 : $item->respuesta;

            if (!isset($valoraciones[$item->pregunta])) {
                $valoraciones[$item->pregunta] = [
                    'pregunta_id' => $item->id_pregunta,
                    'pregunta' => $item->pregunta, // Descripción de la pregunta.
                    'calificacion' => 0, // Calificación redondeada
                    'promedio' => 0, // subtotal / cantidad de valoraciones.
                    'subtotal' => 0, // Sumatoria de valoraciones.
                    'valoraciones' => [], // Valoraciones de cada usuario.
                ];
            }

            $valoraciones[$item->pregunta]['valoraciones'][] = $item->respuesta;
            $valoraciones[$item->pregunta]['subtotal'] += $item->respuesta;
            $valoraciones[$item->pregunta]['promedio'] = $valoraciones[$item->pregunta]['subtotal'] / count($valoraciones[$item->pregunta]['valoraciones']);
            $valoraciones[$item->pregunta]['calificacion'] = ceil($valoraciones[$item->pregunta]['promedio']);
            $valoraciones[$item->pregunta]['promedio'] = round($valoraciones[$item->pregunta]['promedio'], 2);
        }

        return response()->json([
            'producto_id' => $producto,
            'cantidad_valoraciones' => $data_usuarios->count(),
            'usuarios' => $data_usuarios,
            'valoraciones' => array_values($valoraciones),
        ], 200);
    }

    public function getValoraciones($catalogo)
    {
        $validate_catalogo = Catalogo::find($catalogo);
        if ($validate_catalogo) {
            $usuario = auth()->user();
            $encuesta_preguntas = Encuesta::select('id_form', 'catalogo', 'preguntas.*', 'valoraciones.respuesta')
                ->where('catalogo', $catalogo)
                ->where('estado', 'activo')
                ->join('preguntas', 'encuesta', '=', 'id_form')
                ->join('valoraciones', 'valoraciones.pregunta', '=', 'preguntas.id_pregunta')
                ->get();

            $validate_user = Encuesta::select('id_form', 'catalogo', 'preguntas.*')
                ->join('preguntas', 'encuesta', '=', 'id_form')
                ->join('valoraciones', 'valoraciones.pregunta', '=', 'id_pregunta')
                ->where('catalogo', $catalogo)
                ->where('estado', 'activo')
                ->where('usuario', $usuario->id)
                ->exists();

            $response = [
                'response' => 'success',
                'status' => 200,
                'preguntas' => $encuesta_preguntas,
                'respuesta_usuario' => $validate_user,
            ];
        } else {
            $response = [
                'response' => 'error',
                'status' => 403,
                'preguntas' => 'Catalogo no existe en base de datos.',
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
    public function storePreguntas(Request $request)
    {
        $request->validate([
            'usuario' => 'exists:App\Entities\User,id|required',
            'producto' => 'exists:App\Entities\Producto,id_producto|required',
            'preguntas' => 'required|array|min:1',
            'preguntas.*.respuesta' => 'required|integer|min:1|max:5',
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

        return response()->json($response, 200);
    }

    public function destroyPregunta($id_pregunta)
    {
        $validate_pregunta = Valoracion::where('pregunta', $id_pregunta)->exists();

        if ($validate_pregunta) {
            $response = [
                'response' => 'error',
                'status' => 403,
                'message' => 'La pregunta tiene respuestas diligenciadas. No se puede eliminar.',
            ];
        } else {
            $pregunta = Preguntas::find($id_pregunta);

            if ($pregunta) {
                $pregunta->delete();
                $response = [
                    'response' => 'success',
                    'status' => 200,
                ];
            } else {
                $response = [
                    'response' => 'error',
                    'status' => 403,
                    'message' => 'La pregunta enviada no existe.',
                ];
            }
        }

        return response()->json($response);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Entities\Encuesta  $encuesta
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $preguntas = DB::table('preguntas')
            ->select('id_pregunta', 'preguntas.pregunta', DB::raw('COUNT(id_pregunta) AS cant_respuestas, round(avg(respuesta)) AS promedio'))
            ->join('valoraciones', 'valoraciones.pregunta', '=', 'id_pregunta')
            ->where('encuesta', $id)
            ->groupBy('id_pregunta', 'preguntas.pregunta')
            ->get();

        if (count($preguntas) == 0) {
            # code...
            $preguntas = DB::table('preguntas')
                ->select('id_pregunta', 'preguntas.pregunta')
                ->where('encuesta', $id)
                ->groupBy('id_pregunta', 'preguntas.pregunta')
                ->get();
        }

        // Estadistica de Dona - cantidad de usuarios.
        $usuarios_diligenciados = DB::table('preguntas')
            ->select('usuario')
            ->join('valoraciones', 'valoraciones.pregunta', '=', 'id_pregunta')
            ->where('encuesta', $id)
            ->groupBy('usuario')
            ->get();

        // Solo usuarios tipo cliente.
        $usuarios_totales = User::where('rol_id', '3')->count();

        $porcentaje_diligenciado = (count($usuarios_diligenciados) / $usuarios_totales) * 100;

        foreach ($preguntas as $key => $pregunta) {
            // Estadistica de barras - promedio de respuesta.
            $promedio_respuesta = DB::table('preguntas')
                ->select('id_pregunta', 'respuesta', DB::raw('round(AVG(respuesta)) AS promedio'))
                ->join('valoraciones', 'valoraciones.pregunta', '=', 'preguntas.id_pregunta')
                ->where('id_pregunta', $pregunta->id_pregunta)
                ->groupBy('respuesta', 'id_pregunta')
                ->get();

            if (count($promedio_respuesta) < 5) {
                for ($i = 1; $i <= 5; $i++) {
                    $exists = false;
                    foreach ($promedio_respuesta as $key => $respuesta) {
                        if ($i == $respuesta->respuesta) {
                            $exists = true;
                        }
                    }

                    if (!$exists) {
                        $data = new \stdClass;
                        $data->id_pregunta = $pregunta->id_pregunta;
                        $data->respuesta = $i;
                        $data->promedio = 0;
                        $promedio_respuesta->push($data);
                    }

                }
                $pregunta->promedio_respuestas = $promedio_respuesta;
            }
        }

        $response = [
            'response' => 'success',
            'status' => 200,
            'preguntas' => $preguntas,
            'porcentaje_diligenciados' => $porcentaje_diligenciado,
        ];

        return response()->json($response);
    }

    public function edit($id)
    {
        $encuesta = Encuesta::select('encuestas.*', 'catalogos.titulo')
            ->join('catalogos', 'catalogo', '=', 'id_catalogo')
            ->where('id_form', $id)
            ->first();

        $encuesta->preguntas = Encuesta::select('id_form', 'catalogo', 'preguntas.*')
            ->where('catalogo', $encuesta->catalogo)
            ->where('preguntas.encuesta', $id)
            ->join('preguntas', 'encuesta', '=', 'id_form')
            ->get();

        $response = [
            'response' => 'success',
            'status' => 200,
            'encuesta' => $encuesta,
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
        $request->validate([
            'codigo' => 'required',
            'catalogo' => 'required',
            'tipo' => 'required',
            'estado' => 'required',
            'preguntas' => 'required|array',
        ]);

        $encuesta->tipo = $request['tipo'];
        $encuesta->estado = $request['estado'];
        $encuesta->catalogo = $request['catalogo'];
        if ($encuesta->save()) {

            foreach ($request['preguntas'] as $data) {
                if (isset($data['id_pregunta'])) {
                    $pregunta = Preguntas::find($data['id_pregunta']);
                } else {
                    $pregunta = new Preguntas;
                }
                $pregunta->pregunta = $data['pregunta'];
                $pregunta->encuesta = $encuesta->id_form;
                $pregunta->save();
            }

            $response = [
                'response' => 'success',
                'status' => 200,
            ];
        } else {
            $response = [
                'response' => 'error',
                'status' => 403,
                'message' => 'Error en la actualización',
            ];
        }

        return response()->json($response);

    }

}
