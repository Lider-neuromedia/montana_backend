<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Entities\Pqrs;
use DB;

class PqrsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request){
       
        $pqrs = Pqrs::select('id_pqrs', 'codigo', 'fecha_registro', 'ven.name AS name_vendedor', 
        'ven.apellidos AS apellidos_vendedor', 'cli.name AS name_cliente', 'cli.apellidos AS apellidos_cliente', 'estado')
        ->join('users AS ven', 'vendedor', '=','ven.id')
        ->join('users AS cli', 'cliente', '=','cli.id')
        ->get();
        
        $response = [
            'response' => 'success',
            'status' => 200,
            'pqrs' => $pqrs
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
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
