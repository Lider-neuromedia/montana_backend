<?php

namespace App\Http\Controllers;

use App\Entities\User;
use App\Entities\UserData;
use Illuminate\Http\Request;

// use App\Entities\Rol;

use Illuminate\Support\Collection;
use Illuminate\Support\Arr;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(){
        return User::all();
        // $user = User::all();
        // // $rol = Rol::all();
        // // $data = Arr::collapse([$user,$rol]);
        // return $user;
    }

    public function getForRole($rol){
        $userdata = User::where('rol_id',$rol)->get();
        return $userdata;
    }

    public function getVendedores(){
        $userdata = User::where('rol_id',2)->get();
        return $userdata;
    }

    public function getClientes(){
        $userdata = User::where('rol_id',3)->get();
        return $userdata;
    }

    public function getVendedor($id){
        $user = User::where('id',$id)->get();
        $metadata = UserData::where('user_id',$id)->get();
        //return $metadata;

        return response()->json([
            'rol' => $user[0]->rol_id,
            'nombres' => $metadata[0]->value_key,
            'apellidos' => $metadata[1]->value_key,
            'telefono' => $metadata[2]->value_key,
            'email' => $user[0]->email,
            'ciudad' => $metadata[4]->value_key,
        ]);

    }

    public function getCliente($id){
        $user = User::where('id',$id)->get();
        $metadata = UserData::where('user_id',$id)->get();

        $filterData = [];

        foreach($metadata as $mt){
            $filterData[] = [
                'field_key' => $mt->field_key,
                'value_key' => $mt->value_key
            ];
        }

        return response()->json(
            $filterData
        );

    }

    public function createAdmin(Request $request){

        $request->validate([
            'rol_id'   => 'required',
            'name'     => 'required|string',
            'email'    => 'required|string|email|unique:users',
            'password' => 'required|string',
        ]);

        $user = User::create([
            //'rol_id' => $request->rol,
            'rol_id' => 1,
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password)
        ]);

        $userdata = UserData::create([
            'user_id' => $user->id,
            'field_key' => 'default',
            'value_key' => $request->name,
        ]);

        return response()->json([
            'message' => 'Successfully created user!'
        ], 201);

    }


    // public function getForRole($rol){
    //     $userdata = User::where('rol_id',$rol)->get();
    //     return $userdata;
    // }

    // public function getForRole($rol){
    //     $userdata = User::where('rol_id',$rol)->get();
    //     return $userdata;
    // }

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
    {
        $user = User::create($request->all());
        return $user;
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $user = User::find($id);
        return $user;
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
    public function update(Request $request, User $user)
    {
        $user->update($request->all());
        return $user;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $user)
    {
        $userData->delete();
        return $userData;
    }
}
