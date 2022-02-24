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
        if (isset($request['search'])) {
            $search = $request['search'];
        } else {
            $search = "";
        }

        $solicitudes = AmpliacionCupo::select(
            'id_cupo', 'codigo_solicitud', 'fecha_solicitud', 'vendedor',
            'cliente', 'doc_identidad', 'doc_rut', 'doc_camara_com',
            'monto', 'estado', 'vend.name', 'vend.apellidos')
            ->join('users as vend', 'vendedor', '=', 'vend.id')
            ->where('codigo_solicitud', 'like', "%$search%")
            ->orWhere('vend.name', 'like', "%$search%")
            ->orWhere('vend.apellidos', 'like', "%$search%")
            ->orWhere('estado', 'like', "%$search%")
            ->orWhere('monto', 'like', "%$search%")
            ->get();

        foreach ($solicitudes as $solicitud) {
            $solicitud->doc_identidad = url($solicitud->doc_identidad);
            $solicitud->doc_rut = url($solicitud->doc_rut);
            $solicitud->doc_camara_com = url($solicitud->doc_camara_com);
        }

        return response()->json([
            'response' => 'success',
            'status' => 200,
            'solicitudes' => $solicitudes,
        ], 200);
    }

    public function usersByRole($rol_id)
    {
        $users = User::query()
            ->select('id', 'name', 'apellidos')
            ->where('rol_id', $rol_id)
            ->get();

        return response()->json([
            'response' => 'success',
            'status' => 200,
            'users' => $users,
        ], 200);
    }

    public function saveImage($image, $id_cliente, $name)
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
            'doc_identidad' => ['required', 'file', 'max:2000'],
            'doc_rut' => ['required', 'file', 'max:2000'],
            'doc_camara_com' => ['required', 'file', 'max:2000'],
            'monto' => ['required', 'integer'],
        ]);

        $solicitud = new AmpliacionCupo();
        $solicitud->codigo_solicitud = uniqid();
        $solicitud->fecha_solicitud = date('Y-m-d');
        $solicitud->vendedor = $request['vendedor'];
        $solicitud->cliente = $request['cliente'];
        $solicitud->monto = $request['monto'];
        $solicitud->estado = 'pendiente';

        // Guardar imagenes.
        $path_solicitud = "storage/solicitudes/{$request['cliente']}/";
        $solicitud->doc_identidad = $path_solicitud . $this->saveImage($request->file('doc_identidad'), $request['cliente'], 'doc_identidad');
        $solicitud->doc_rut = $path_solicitud . $this->saveImage($request->file('doc_rut'), $request['cliente'], 'doc_rut');
        $solicitud->doc_camara_com = $path_solicitud . $this->saveImage($request->file('doc_camara_com'), $request['cliente'], 'doc_camara_comercio');
        $solicitud->save();

        return response()->json([
            'response' => 'success',
            'status' => 200,
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'vendedor' => ['required', 'exists:users,id'],
            'cliente' => ['required', 'exists:users,id'],
            'monto' => ['required'],
        ]);

        $solicitud = AmpliacionCupo::findOrFail($id);
        $solicitud->vendedor = $request['vendedor'];
        $solicitud->cliente = $request['cliente'];
        $solicitud->monto = $request['monto'];

        // Guardar imagenes.
        $path_solicitud = "storage/solicitudes/{$request['cliente']}/";

        if (isset($request['doc_identidad']) && $request['doc_identidad'] != '') {
            $solicitud->doc_identidad = $path_solicitud . $this->saveImage($request->file('doc_identidad'), $request['cliente'], 'doc_identidad');
        }
        if (isset($request['doc_rut']) && $request['doc_rut'] != '') {
            $solicitud->doc_rut = $path_solicitud . $this->saveImage($request->file('doc_rut'), $request['cliente'], 'doc_rut');
        }
        if (isset($request['doc_camara_com']) && $request['doc_camara_com'] != '') {
            $solicitud->doc_camara_com = $path_solicitud . $this->saveImage($request->file('doc_camara_com'), $request['cliente'], 'doc_camara_comercio');
        }

        $solicitud->save();

        return response()->json([
            'response' => 'success',
            'status' => 200,
        ], 200);
    }

    public function changeState($solicitud, $estado)
    {
        $solicitud_db = AmpliacionCupo::findOrFail($solicitud);
        $solicitud_db->estado = $estado;
        $solicitud_db->save();

        return response()->json([
            'response' => 'success',
            'status' => 200,
        ], 200);
    }
}
