<?php

namespace App\Http\Controllers;

use App\Entities\AmpliacionCupo;
use App\Entities\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AmpliacionCupoController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search') ?: '';

        $solicitudes = AmpliacionCupo::query()
            ->when($search, function ($q) use ($search) {
                $q->where('codigo_solicitud', 'like', "%$search%")
                    ->orWhere('estado', 'like', "%$search%")
                    ->orWhere('monto', 'like', "%$search%")
                    ->orWhereHas('ampliacionVendedor', function ($q) use ($search) {
                        $q->where('name', 'like', "%$search%")
                            ->orWhere('apellidos', 'like', "%$search%")
                            ->orWhere('email', 'like', "%$search%")
                            ->orWhere('dni', 'like', "%$search%");
                    })
                    ->orWhereHas('ampliacionCliente', function ($q) use ($search) {
                        $q->where('name', 'like', "%$search%")
                            ->orWhere('apellidos', 'like', "%$search%")
                            ->orWhere('email', 'like', "%$search%")
                            ->orWhere('dni', 'like', "%$search%");
                    });
            })
            ->with('ampliacionVendedor', 'ampliacionCliente')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $solicitudes->setCollection(
            $solicitudes->getCollection()
                ->map(function ($x) {
                    $x->vendedor = $x->ampliacionVendedor;
                    $x->cliente = $x->ampliacionCliente;
                    unset($x->ampliacionVendedor);
                    unset($x->ampliacionCliente);

                    $x->doc_identidad = url($x->doc_identidad);
                    $x->doc_rut = url($x->doc_rut);
                    $x->doc_camara_com = url($x->doc_camara_com);
                    return $x;
                })
        );

        return response()->json([
            'response' => 'success',
            'status' => 200,
            'solicitudes' => $solicitudes,
        ], 200);
    }

    public function usuariosPorRol($rol_id)
    {
        $users = User::query()
            ->select('id', 'name', 'apellidos')
            ->where('rol_id', $rol_id)
            ->orderBy('name', 'asc')
            ->orderBy('apellidos', 'asc')
            ->paginate(20);

        return response()->json([
            'response' => 'success',
            'status' => 200,
            'users' => $users,
        ], 200);
    }

    private function saveImage($image, $id_cliente, $name)
    {
        $extension = array_reverse(explode(".", $image->getClientOriginalName()))[0];
        $filecontent = file_get_contents($image->getRealPath());

        $timestamp = Carbon::now()->format('YmdHis');
        $filename = "$name-$timestamp.$extension";
        $path = public_path("storage/solicitudes/{$id_cliente}");

        if (!is_dir($path)) {
            mkdir($path);
        }

        \Storage::disk('solicitudes')->put("/{$id_cliente}/$filename", $filecontent);

        return $filename;
    }

    public function store(Request $request)
    {
        $request->validate([
            'vendedor' => ['required', 'exists:users,id'],
            'cliente' => ['required', 'exists:users,id'],
            'monto' => ['required', 'integer', 'min:0'],
            'doc_identidad' => ['required', 'file', 'max:2000'],
            'doc_rut' => ['required', 'file', 'max:2000'],
            'doc_camara_com' => ['required', 'file', 'max:2000'],
        ]);

        return $this->saveOrUpdate($request);
    }

    public function update(Request $request, AmpliacionCupo $ampliacion_cupo)
    {
        $request->validate([
            'vendedor' => ['required', 'exists:users,id'],
            'cliente' => ['required', 'exists:users,id'],
            'monto' => ['required', 'integer', 'min:0'],
            'doc_identidad' => ['nullable', 'file', 'max:2000'],
            'doc_rut' => ['nullable', 'file', 'max:2000'],
            'doc_camara_com' => ['nullable', 'file', 'max:2000'],
        ]);

        return $this->saveOrUpdate($request, $ampliacion_cupo);
    }

    private function saveOrUpdate(Request $request, AmpliacionCupo $solicitud = null)
    {
        try {

            \DB::beginTransaction();

            $vendedor = User::findOrFail($request->get('vendedor'));
            $cliente = User::findOrFail($request->get('cliente'));

            // Guardar imagenes.
            $path = "storage/solicitudes/{$cliente->id}";
            $fileIdentidad = null;
            $fileRUT = null;
            $fileCamaraCom = null;

            if ($solicitud != null) {
                $fileIdentidad = $solicitud->doc_identidad;
                $fileRUT = $solicitud->doc_rut;
                $fileCamaraCom = $solicitud->doc_camara_com;
            }

            if ($request->hasFile('doc_identidad')) {
                $fileIdentidad = $this->saveImage(
                    $request->file('doc_identidad'),
                    $cliente->id,
                    'doc_identidad');
                $fileIdentidad = "$path/$fileIdentidad";
            }
            if ($request->hasFile('doc_rut')) {
                $fileRUT = $this->saveImage(
                    $request->file('doc_rut'),
                    $cliente->id,
                    'doc_rut');
                $fileRUT = "$path/$fileRUT";
            }
            if ($request->hasFile('doc_camara_com')) {
                $fileCamaraCom = $this->saveImage(
                    $request->file('doc_camara_com'),
                    $cliente->id,
                    'doc_camara_com');
                $fileCamaraCom = "$path/$fileCamaraCom";
            }

            if ($solicitud == null) {
                $solicitud = new AmpliacionCupo([
                    'codigo_solicitud' => uniqid(),
                    'fecha_solicitud' => Carbon::now()->format('Y-m-d'),
                    'monto' => $request->get('monto'),
                    'estado' => 'pendiente',
                    'doc_identidad' => $fileIdentidad,
                    'doc_rut' => $fileRUT,
                    'doc_camara_com' => $fileCamaraCom,
                ]);
            } else {
                $solicitud->update([
                    'monto' => $request->get('monto'),
                    'estado' => 'pendiente',
                    'doc_identidad' => $fileIdentidad,
                    'doc_rut' => $fileRUT,
                    'doc_camara_com' => $fileCamaraCom,
                ]);
            }

            $solicitud->ampliacionVendedor()->associate($vendedor);
            $solicitud->ampliacionCliente()->associate($cliente);
            $solicitud->save();

            \DB::commit();

            return response()->json([
                'ampliacion_cupo_id' => $solicitud->id_cupo,
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

    public function changeState(AmpliacionCupo $solicitud, $estado)
    {
        if (!in_array($estado, ['aceptado', 'rechazado', 'pendiente'])) {
            return response()->json([
                'response' => 'success',
                'message' => "Estado no reconocido",
                'status' => 500,
            ], 500);
        }

        $solicitud->update([
            'estado' => $estado,
        ]);

        return response()->json([
            'response' => 'success',
            'status' => 200,
        ], 200);
    }
}
