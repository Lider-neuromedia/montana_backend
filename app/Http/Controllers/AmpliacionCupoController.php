<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Entities\AmpliacionCupo;
use DB;
use Carbon\Carbon;

class AmpliacionCupoController extends Controller{

    public function index(Request $request){

        if (isset($request['search'])) {
            $search = $request['search'];
        }else{
            $search = "";
        }

        $solicitudes = AmpliacionCupo::select('id_cupo','codigo_solicitud', 'fecha_solicitud',
                'vendedor', 'cliente', 'doc_identidad', 'doc_rut', 'doc_camara_com', 'monto',
                'estado', 'vend.name', 'vend.apellidos')
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

        $response = [
            'response' => 'success',
            'status' => 200,
            'solicitudes' => $solicitudes
        ];

        return response()->json($response);
    }

    public function getUserSmall($rol_id){
        $users = DB::table('users')->select('id', 'name', 'apellidos')
        ->where('rol_id', $rol_id)
        ->get();

        $response = [
            'response' => 'success',
            'status' => 200,
            'users' => $users
        ];

        return response()->json($response);
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

    public function store(Request $request){
        $request->validate([
            'vendedor' => 'required|exists:App\Entities\User,id',
            'cliente' => 'required|exists:App\Entities\User,id',
            'doc_identidad' => 'required|file',
            'doc_rut' => 'required|file',
            'doc_camara_com' => 'required|file',
            'monto' => 'required|integer',
        ]);

        $solicitud = new AmpliacionCupo;
        $solicitud->codigo_solicitud = uniqid();
        $solicitud->fecha_solicitud = date('Y-m-d');
        $solicitud->vendedor = $request['vendedor'];
        $solicitud->cliente = $request['cliente'];
        $solicitud->monto = $request['monto'];
        $solicitud->estado = 'pendiente';

        // Guardar imagenes.
        $path_solicitud = "storage/solicitudes/{$request['cliente']}/";
        $solicitud->doc_identidad = $path_solicitud . $this->saveImage($request->file('doc_identidad'), $request['cliente'],'doc_identidad');
        $solicitud->doc_rut = $path_solicitud . $this->saveImage($request->file('doc_rut'), $request['cliente'], 'doc_rut');
        $solicitud->doc_camara_com = $path_solicitud . $this->saveImage($request->file('doc_camara_com'), $request['cliente'],'doc_camara_comercio');

        if($solicitud->save()){
            $response = [
                'response' => 'success',
                'status' => 200,
            ];
        }else{
            $response = [
                'response' => 'error',
                'status' => 403,
                'message' => 'Error con la creación de la solicitud. Intente nuevamente'
            ];
        }

        return response()->json($response);

    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'vendedor' => 'exists:App\Entities\User,id|required',
            'cliente' => 'exists:App\Entities\User,id|required',
            'monto' => 'required',
        ]);

        $solicitud = AmpliacionCupo::find($id);
        $solicitud->vendedor = $request['vendedor'];
        $solicitud->cliente = $request['cliente'];
        $solicitud->monto = $request['monto'];

        // Guardar imagenes.
        $path_solicitud = "storage/solicitudes/{$request['cliente']}/";
        if (isset($request['doc_identidad']) && $request['doc_identidad'] != '') {
            $solicitud->doc_identidad = $path_solicitud . $this->saveImage($request->file('doc_identidad'), $request['cliente'],'doc_identidad');
        }
        if (isset($request['doc_rut']) && $request['doc_rut'] != '') {
            $solicitud->doc_rut = $path_solicitud . $this->saveImage($request->file('doc_rut'), $request['cliente'], 'doc_rut');
        }
        if (isset($request['doc_camara_com']) && $request['doc_camara_com'] != '') {
            $solicitud->doc_camara_com = $path_solicitud . $this->saveImage($request->file('doc_camara_com'), $request['cliente'],'doc_camara_comercio');
        }

        if($solicitud->save()){
            $response = [
                'response' => 'success',
                'status' => 200,
            ];
        }else{
            $response = [
                'response' => 'error',
                'status' => 403,
                'message' => 'Error con la actualización de la solicitud. Intente nuevamente'
            ];
        }

        return response()->json($response);

    }

    public function changeState($solicitud, $estado){
        $solicitud_db = AmpliacionCupo::find($solicitud);
        $solicitud_db->estado = $estado;

        if($solicitud_db->save()){
            $response = ['response' => 'success', 'status' => 200];
        }else{
            $response = ['response' => 'error', 'status' => 403, 'message' => 'Error en el cambio de estado.'];
        }

        return response()->json($response);
    }

}
