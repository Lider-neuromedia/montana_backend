<?php

namespace App\Http\Controllers;

use App\Entities\Catalogo;
use App\Entities\Producto;
use App\Utils\Utils;
use Illuminate\Http\Request;

class CatalogoController extends Controller
{
    /**
     * Listado de todos los catalogos.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        Utils::corregirCantidadDeProductosEnCatalogos();
        $catalogos = Catalogo::select('*');

        if (isset($request['search'])) {
            $search = json_decode($request['search'], true);
            $filtro_catalogos = [];
            $filtro_publico = [];
            $filtro_etiquetas = [];

            if (isset($search['general']) && $search['general'] === true) {
                $filtro_catalogos[] = 'general';
            }
            if (isset($search['show_room']) && $search['show_room'] === true) {
                $filtro_catalogos[] = 'show room';
            }
            if (isset($search['public']) && $search['public'] === true) {
                $filtro_publico[] = 'activo';
            }
            if (isset($search['private']) && $search['private'] === true) {
                $filtro_publico[] = 'privado';
            }
            if (isset($search['ninos']) && $search['ninos'] === true) {
                $filtro_etiquetas[] = 'niños';
            }
            if (isset($search['adultos']) && $search['adultos'] === true) {
                $filtro_etiquetas[] = 'adultos';
            }

            if (count($filtro_catalogos) > 0) {
                $catalogos->whereIn('tipo', $filtro_catalogos);
            }
            if (count($filtro_publico) > 0) {
                $catalogos->whereIn('estado', $filtro_publico);
            }
            if (count($filtro_etiquetas) > 0) {
                $catalogos->whereIn('etiqueta', $filtro_etiquetas);
            }

            $catalogos = $catalogos->get();
        } else {
            $catalogos = Catalogo::all();
        }

        // Setear la url de la imagen segun servidor.
        foreach ($catalogos as $catalogo) {
            $catalogo->imagen = url($catalogo->imagen);
        }

        return response()->json([
            'response' => 'success',
            'message' => '',
            'status' => 200,
            'catalogos' => $catalogos,
        ], 200);
    }

    /**
     * Crear un nuevo catalogo.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required',
            'estado' => 'required',
            'tipo' => 'required',
            'image' => 'required',
            'etiqueta' => 'required|string|max:100|in:adultos,niños',
            'descuento' => 'nullable|integer|min:0|max:99',
        ]);

        $catalogo = new Catalogo();
        $catalogo->titulo = $request['nombre'];
        $catalogo->etiqueta = $request['etiqueta'];

        if ($request['tipo'] == 'show room' && $request['estado'] == 'activo') {
            $validate_show_room = Catalogo::where('tipo', 'show room')->where('estado', 'activo')->exists();

            if (!$validate_show_room) {
                // Si no existe un catalogo show room activo sigue normal.
                $catalogo->estado = $request['estado'];
            } else {
                // Si existe. No se crea el catalogo y se devuelve al front.
                return response()->json([
                    'response' => 'warning',
                    'status' => 200,
                    'message' => 'Solo debe existir un catalogo show room activo. Por favor inactive el anterior catalogo antes de activar uno nuevo.',
                ], 200);
            }
        } else {
            $catalogo->estado = $request['estado'];
        }

        $catalogo->tipo = $request['tipo'];
        $catalogo->cantidad = 0;
        $catalogo->descuento = $request->get('descuento') ? $request->get('descuento') : null;
        $catalogo->save();
        $filename = $this->saveImage($request->file('image'), $catalogo->id_catalogo);
        $catalogo->imagen = "storage/catalogos/{$filename}";
        $catalogo->save();

        return response()->json([
            'catalogo' => $catalogo,
            'response' => 'success',
            'status' => 200,
        ], 200);
    }

    public function saveImage($image, $id_catalogo)
    {
        $extension = array_reverse(explode(".", $image->getClientOriginalName()))[0];
        $filecontent = file_get_contents($image->getRealPath());
        $filename = "$id_catalogo.$extension";
        \Storage::disk('catalogos')->put($filename, $filecontent);
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
        $catalogo = Catalogo::findOrFail($id);
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
    public function update(Request $request)
    {
        $request->validate([
            'id_catalogo' => 'required|exists:catalogos,id_catalogo',
            'titulo' => 'required',
            'estado' => 'required',
            'tipo' => 'required',
            'imagen' => 'nullable|image',
            'etiqueta' => 'required|string|max:100|in:adultos,niños',
            'descuento' => 'nullable|integer|min:0|max:99',
        ]);

        $catalogo = Catalogo::findOrFail($request['id_catalogo']);
        $catalogo->titulo = $request['titulo'];
        $catalogo->etiqueta = $request['etiqueta'];

        if ($request['tipo'] == 'show room' && $request['estado'] == 'activo') {
            $validate_show_room = Catalogo::where('tipo', 'show room')->where('estado', 'activo');
            if (!$validate_show_room->exists()) {
                // Si no existe un catalogo show room activo sigue normal.
                $catalogo->estado = $request['estado'];
            } else {
                // Validar que el catalogo no sea el mismo.
                if ($validate_show_room->first()->id_catalogo == $request['id_catalogo']) {
                    $catalogo->estado = $request['estado'];
                } else {
                    // Si existe. Actualiza lo demas y retorna una advertencia.
                    $catalogo->tipo = $request['tipo'];
                    $catalogo->descuento = $request->get('descuento') ? $request->get('descuento') : $catalogo->descuento;
                    $catalogo->save();

                    return response()->json([
                        'response' => 'warning',
                        'status' => 200,
                        'message' => 'Solo debe existir un catalogo show room activo. Por favor inactive el anterior catalogo antes de activar uno nuevo.',
                    ], 200);
                }
            }

        } else {
            $catalogo->estado = $request['estado'];
        }

        $catalogo->tipo = $request['tipo'];
        $catalogo->descuento = (isset($request['descuento'])) ? $request['descuento'] : $catalogo->descuento;
        $catalogo->save();

        if ($request->hasFile('imagen')) {
            $filename = $this->saveImage($request->file('imagen'), $catalogo->id_catalogo);
            $catalogo->imagen = "storage/catalogos/{$filename}";
            $catalogo->save();
        }

        return response()->json([
            'catalogo' => $catalogo,
            'response' => 'success',
            'status' => 200,
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Entities\Catalogo  $catalogo
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $catalogo = Catalogo::findOrFail($id);
        $catalogo->cantidad = Producto::where('catalogo', $id)->count();
        $catalogo->save();

        if ($catalogo->cantidad == 0) {
            $catalogo->delete();

            return response()->json([
                'response' => 'success',
                'status' => 200,
            ], 200);
        }

        return response()->json([
            'response' => 'error',
            'status' => 401,
            'message' => 'El catalogo tiene productos registrados. No se puede eliminar.',
        ], 401);
    }

    public function consumerCatalogos()
    {
        $catalogos = Catalogo::query()
            ->where('estado', 'activo')
            ->where('cantidad', '!=', 0)
            ->get();

        foreach ($catalogos as $catalogo) {
            $catalogo->imagen = url($catalogo->imagen);
        }

        return response()->json([
            'response' => 'success',
            'status' => 200,
            'catalogos' => $catalogos,
        ], 200);
    }
}
