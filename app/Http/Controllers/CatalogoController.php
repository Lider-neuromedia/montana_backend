<?php

namespace App\Http\Controllers;

use App\Entities\Catalogo;
use Illuminate\Http\Request;

class CatalogoController extends Controller
{
    private function saveImage($image, $id_catalogo)
    {
        $extension = array_reverse(explode(".", $image->getClientOriginalName()))[0];
        $filecontent = file_get_contents($image->getRealPath());
        $filename = "$id_catalogo.$extension";
        \Storage::disk('catalogos')->put($filename, $filecontent);
        return $filename;
    }

    public function catalogosActivos()
    {
        $catalogos = Catalogo::query()
            ->where('estado', 'activo')
            ->where('cantidad', '!=', 0)
            ->orderBy('titulo', 'asc')
            ->get()
            ->map(function ($x) {
                $x->imagen = url($x->imagen);
                return $x;
            });

        return response()->json([
            'response' => 'success',
            'status' => 200,
            'catalogos' => $catalogos,
        ], 200);
    }

    public function index(Request $request)
    {
        $filtro_catalogos = collect([]);
        $filtro_publico = collect([]);
        $filtro_etiquetas = collect([]);

        if ($request->has('search') && $request->get('search')) {
            $search = json_decode($request->get('search'), true);

            if (isset($search['general']) && $search['general'] == 1) {
                $filtro_catalogos->push('general');
            }
            if (isset($search['show_room']) && $search['show_room'] == 1) {
                $filtro_catalogos->push('show room');
            }
            if (isset($search['public']) && $search['public'] == 1) {
                $filtro_publico->push('activo');
            }
            if (isset($search['private']) && $search['private'] == 1) {
                $filtro_publico->push('privado');
            }
            if (isset($search['ninos']) && $search['ninos'] == 1) {
                $filtro_etiquetas->push('niños');
            }
            if (isset($search['adultos']) && $search['adultos'] == 1) {
                $filtro_etiquetas->push('adultos');
            }
        }

        $catalogos = Catalogo::query()
            ->when($filtro_catalogos->isNotEmpty(), function ($q) use ($filtro_catalogos) {
                $q->whereIn('tipo', $filtro_catalogos);
            })
            ->when($filtro_publico->isNotEmpty(), function ($q) use ($filtro_publico) {
                $q->whereIn('estado', $filtro_publico);
            })
            ->when($filtro_etiquetas->isNotEmpty(), function ($q) use ($filtro_etiquetas) {
                $q->whereIn('etiqueta', $filtro_etiquetas);
            })
            ->orderBy('titulo', 'asc')
            ->get()
            ->map(function ($x) {
                $x->imagen = url($x->imagen);
                return $x;
            });

        return response()->json([
            'response' => 'success',
            'status' => 200,
            'catalogos' => $catalogos,
        ], 200);
    }

    public function show(Catalogo $catalogo)
    {
        return response()->json($catalogo, 200);
    }

    public function store(Request $request)
    {
        $estados = implode(',', ['activo', 'privado']);
        $tipos = implode(',', ['general', 'show room']);
        $etiquetas = implode(',', ['adultos', 'niños']);

        $request->validate([
            'estado' => ['required', 'string', 'max:25', "in:$estados"],
            'tipo' => ['required', 'string', 'max:25', "in:$tipos"],
            'etiqueta' => ['required', 'string', 'max:45', "in:$etiquetas"],
            'titulo' => ['required', 'string', 'max:45'],
            'imagen' => ['required', 'file', 'max:2000'],
        ]);

        return $this->saveOrUpdate($request);
    }

    public function update(Request $request, Catalogo $catalogo)
    {
        $estados = implode(',', ['activo', 'privado']);
        $tipos = implode(',', ['general', 'show room']);
        $etiquetas = implode(',', ['adultos', 'niños']);

        $request->validate([
            'estado' => ['required', 'string', 'max:25', "in:$estados"],
            'tipo' => ['required', 'string', 'max:25', "in:$tipos"],
            'etiqueta' => ['required', 'string', 'max:45', "in:$etiquetas"],
            'titulo' => ['required', 'string', 'max:45'],
            'imagen' => ['nullable', 'file', 'max:2000'],
        ]);

        return $this->saveOrUpdate($request, $catalogo);
    }

    private function saveOrUpdate(Request $request, Catalogo $catalogo = null)
    {
        try {

            \DB::beginTransaction();

            // Deshabilitar los otros show room.
            if ($request->get('tipo') == 'show room' && $request->get('estado') == 'activo') {
                Catalogo::query()
                    ->where('tipo', 'show room')
                    ->update([
                        'estado' => 'privado',
                    ]);
            }

            $catalogoData = $request->only('titulo', 'etiqueta', 'tipo', 'estado');

            if ($catalogo == null) {
                $catalogoData['cantidad'] = 0;
                $catalogoData['descuento'] = 0;
                $catalogo = Catalogo::create($catalogoData);
            } else {
                $catalogo->update($catalogoData);
            }

            if ($request->hasFile('imagen')) {
                $filename = $this->saveImage(
                    $request->file('imagen'),
                    $catalogo->id_catalogo);

                $catalogo->update([
                    'imagen' => "storage/catalogos/{$filename}",
                ]);
            }

            \DB::commit();

            return response()->json([
                'catalogo' => $catalogo,
                'response' => 'success',
                'status' => 200,
            ], 200);

        } catch (\Exception $ex) {
            \Log::info($ex->getMessage());
            \Log::info($ex->getTraceAsString());
            \DB::rollBack();

            return response()->json([
                'response' => 'error',
                'message' => $ex->getMessage(),
                'status' => 500,
            ], 500);
        }
    }

    public function destroy(Catalogo $catalogo)
    {
        if ($catalogo->productos()->count() > 0) {
            throw new \Exception("El catalogo tiene productos registrados. No se puede eliminar.", 1);
        }

        $catalogo->delete();

        return response()->json([
            'message' => 'Catálogo borrado correctamente.',
            'response' => 'success',
            'status' => 200,
        ], 200);
    }
}
