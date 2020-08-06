<?php

namespace App\Http\Controllers;

use App\Entities\UserData;
use Illuminate\Http\Request;

use App\Http\Requests\UserDataRequest;

class UserDataController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return UserData::all();
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    // public function store(UserDataRequest $request)
    {

        // return $request->all();

        $request->validate([
            'nombres'   => 'required',
            // 'apellidos'     => 'required',
            // 'tipo_documento'    => 'required',
            // 'numero_documento'    => 'required',
            // 'celular' => 'required',
            // 'codigo' => 'required',
        ]);
        return $request;

        // return $request;
        // $validate = $request->validated();

        // $data = $request->all();
        // foreach($data as $d){

        //     $metadata = UserData::create([
        //         'user_id' => $d['user_id'],
        //         'field_key' => $d['field_key'],
        //         'value_key' => $d['value']
        //     ]);

        // }
        
        // return response()->json([
        //     'messages' => 'Datos enviados correctamente'
        // ]);

    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Entities\UserData  $userData
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $userData = UserData::find($id);
        return $userData;
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Entities\UserData  $userData
     * @return \Illuminate\Http\Response
     */
    public function edit(UserData $userData)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Entities\UserData  $userData
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, UserData $userData)
    {
        $userData->update($request->all());
        return $userData;
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Entities\UserData  $userData
     * @return \Illuminate\Http\Response
     */
    public function destroy(UserData $userData)
    {
        $userData->delete();
        return $userData;
    }
}
