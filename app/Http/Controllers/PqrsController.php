<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Entities\Pqrs;
use App\Entities\SeguimientoPqrs;
use DB;

class PqrsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request){
       
        $search = $request['search'];

        $pqrs = Pqrs::select('id_pqrs', 'codigo', 'fecha_registro', 'ven.name AS name_vendedor', 
        'ven.apellidos AS apellidos_vendedor', 'cli.name AS name_cliente', 'cli.apellidos AS apellidos_cliente', 'estado')
        ->join('users AS ven', 'vendedor', '=','ven.id')
        ->join('users AS cli', 'cliente', '=','cli.id')
        ->where('codigo', 'like', "%$search%")
        ->orWhere('ven.name', 'like', "%$search%")
        ->orWhere('ven.apellidos', 'like', "%$search%")
        ->orWhere('cli.name', 'like', "%$search%")
        ->orWhere('cli.apellidos', 'like', "%$search%")
        ->get();
        
        $response = [
            'response' => 'success',
            'status' => 200,
            'pqrs' => $pqrs
        ];
        
        return response()->json($response);
        
    }

    public function getPqrsUserSesion(){
        $user = auth()->user();
        if ($user != null) {
            $pqrs = Pqrs::select('id_pqrs', 'codigo', 'fecha_registro', 'ven.name AS name_vendedor', 
            'ven.apellidos AS apellidos_vendedor', 'cli.name AS name_cliente', 'cli.apellidos AS apellidos_cliente', 'estado')
            ->join('users AS ven', 'vendedor', '=','ven.id')
            ->join('users AS cli', 'cliente', '=','cli.id')
            ->where('vendedor', '=', $user->id)
            ->get();
            
            $response = [
                'response' => 'success',
                'status' => 200,
                'pqrs' => $pqrs
            ];
        }else{
            $response = [
                'response' => 'error',
                'status' => 403,
                'message' => 'La sesión del usuario a finalizado.'
            ];
        }
        
        
        return response()->json($response, $response['status']);
        
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request){
        $request->validate([
            'vendedor' => 'exists:App\Entities\User,id|required',
            'cliente' => 'exists:App\Entities\User,id|required',
            'tipo' => 'required',
            'mensaje' => 'required'
        ]);

        $pqrs = new Pqrs;
        $pqrs->codigo = uniqid();
        $pqrs->fecha_registro = date('Y-m-d');
        $pqrs->cliente = $request['cliente'];
        $pqrs->vendedor = $request['vendedor'];
        $pqrs->tipo = $request['tipo'];
        $pqrs->estado = 'abierto';

        if ($pqrs->save()) {
            DB::table('seguimiento_pqrs')->insert([
                'usuario' => $request['cliente'],
                'pqrs' => $pqrs->id_pqrs,
                'mensaje' => $request['mensaje'],
                'hora' => time()
            ]);
            $response = [
                'response' => 'success',
                'status' => 200,
            ];
        }else{
            $response = [
                'response' => 'error',
                'status' => 403,
                'message' => 'Error en la creación de la PQRS.',
            ];
        }

        return response()->json($response);

    }   

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id){
        $pqrs = Pqrs::select('id_pqrs', 'codigo', 'fecha_registro', 'pqrs.vendedor', 'pqrs.cliente', 'ven.name AS name_vendedor', 
            'ven.apellidos AS apellidos_vendedor', 'cli.name AS name_cliente', 'cli.apellidos AS apellidos_cliente', 'estado')
            ->join('users AS ven', 'vendedor', '=','ven.id')
            ->join('users AS cli', 'cliente', '=','cli.id')
            ->where('id_pqrs', $id)
            ->first();
        
        $messages_pqrs = DB::table('seguimiento_pqrs')
                        ->select('seguimiento_pqrs.*','users.name', 'users.apellidos', 'users.rol_id')
                        ->join('users', 'usuario', '=', 'id')
                        ->where('pqrs', $id)
                        ->orderBy('seguimiento_pqrs.created_at', 'ASC')
                        ->get();
    

        foreach ($messages_pqrs as $messages) {
            $val_rol_user = auth()->user()->rol_id;
            $messages->iniciales = substr($messages->name, 0, 1);
            $messages->iniciales .= substr($messages->apellidos, 0, 1);
            $messages->hora = substr($messages->hora, 0, -3);

            if ($messages->rol_id == 2) {
                // Si el usuario autenticado es admin o vendedor. Se pone como destinatario o addressee al vendedor.
                if ($val_rol_user == 1 || $val_rol_user == 2) {
                    $messages->addressee = true;
                }else{
                    $messages->addressee = false;
                }
            }else if($messages->rol_id == 3){
                // Si el usuario autenticado es admin o vendedor. pero el mensaje lo diligencio un cliente
                if ($val_rol_user == 1 || $val_rol_user == 2) {
                    $messages->addressee = false;
                }else{
                    $messages->addressee = true;
                }
            }else{
                if ($val_rol_user == 1 || $val_rol_user == 2) {
                    $messages->addressee = true;
                }else{
                    $messages->addressee = false;
                }
            }
        }
        $pqrs->messages_pqrs = $messages_pqrs;
        
        // Busqueda de los pedidos en base al vendedor y cliente registrados en la pqrs.
        $pedidos = DB::table('pedidos')
                    ->select('pedidos.*', 'users.name', 'users.apellidos', 'users.rol_id')
                    ->join('users', 'cliente', '=', 'id')
                    ->where('vendedor', $pqrs->vendedor)
                    ->where('cliente', $pqrs->cliente)
                    ->get();
        foreach ($pedidos as $pedido) {
            $pedido->iniciales = substr($pedido->name, 0, 1);
            $pedido->iniciales .= substr($pedido->apellidos, 0, 1);
        }

        $pqrs->pedidos = $pedidos;

        $response = [
            'response' => 'success',
            'status' => 200,
            'pqrs' => $pqrs
        ];

        return response()->json($response);
    }

    /**
     * Nuevo mensaje
     *
     * @param  Request  $request
     * @return \Illuminate\Http\Response
     */
    public function NewMessage(Request $request){
        $request->validate([
            'mensaje' => 'required',
            'usuario' => 'exists:App\Entities\User,id|required',
            'pqrs' => 'exists:App\Entities\Pqrs,id_pqrs|required', 
        ]);
        $seguimiento = new SeguimientoPqrs;
        $seguimiento->usuario = $request['usuario'];
        $seguimiento->pqrs = $request['pqrs'];
        $seguimiento->mensaje = $request['mensaje'];
        $seguimiento->hora = date('H:i');
        
        if($seguimiento->save()){
            $message_pqrs = SeguimientoPqrs::select('seguimiento_pqrs.*','users.name', 'users.apellidos', 'users.rol_id')
                        ->join('users', 'usuario', '=', 'id')
                        ->where('id_seguimiento', $seguimiento->id_seguimiento)
                        ->first();
                        
            $val_rol_user = auth()->user()->rol_id;
            $message_pqrs->iniciales = substr($message_pqrs->name, 0, 1);
            $message_pqrs->iniciales .= substr($message_pqrs->apellidos, 0, 1);
            $message_pqrs->hora = substr($message_pqrs->hora, 0, -3);

            if ($message_pqrs->rol_id == 2) {
                // Si el usuario autenticado es admin o vendedor. Se pone como destinatario o addressee al vendedor.
                if ($val_rol_user == 1 || $val_rol_user == 2) {
                    $message_pqrs->addressee = true;
                }else{
                    $message_pqrs->addressee = false;
                }
            }else if($message_pqrs->rol_id == 3){
                // Si el usuario autenticado es admin o vendedor. pero el mensaje lo diligencio un cliente
                if ($val_rol_user == 1 || $val_rol_user == 2) {
                    $message_pqrs->addressee = false;
                }else{
                    $message_pqrs->addressee = true;
                }
            }else{
                if ($val_rol_user == 1 || $val_rol_user == 2) {
                    $message_pqrs->addressee = true;
                }else{
                    $message_pqrs->addressee = false;
                }
            }

            $response = [
                'response' => 'success',
                'status' => 200,
                'mensaje' => $message_pqrs
            ];
        }else{
            
            $response = [
                'response' => 'error',
                'status' => 403,
                'message' => 'Error creando el mensaje.'
            ];
        }

        return response()->json($response);

    }

    public function changeState($id, $state){
        $pqrs = Pqrs::find($id);
        if ($state == 'abierto' || $state == 'cerrado') {
            $pqrs->estado = $state;
        }else{
            $response = [
                'response' => 'error',
                'status' => 403,
                'message' => 'El estado enviado no es valido.'
            ];
            return response()->json($response, $response['status']);
        }

        if($pqrs->save()){
            $response = [
                'response' => 'success',
                'status' => 200,
                'message' => 'Estado asignado de manera correcta.'
            ];
        }else{
            $response = [
                'response' => 'error',
                'status' => 403,
                'message' => 'Error en el cambio de estado.'
            ];
        }

        return response()->json($response);
    }
}
