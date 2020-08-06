<?php

namespace App\Http\Controllers;

use App\Entities\User;
use App\Entities\UserData;
use App\Entities\VendedorCliente;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;

use App\Http\Requests\UserRequest;

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
        return User::paginate(4);
        // $user = User::all();
        // // $rol = Rol::all();
        // // $data = Arr::collapse([$user,$rol]);
        // return $user;
    }

    /*
     * Traer usuarios por su tipo de rol
     */
    public function getForRole($rol){

        $userdata = DB::select('
            select * from users
            where rol_id = "'.$rol.'"
        ');
   
        // $userdata = DB::select('
        //     select u.name, u.email, data1.value_key as "telefono", data2.value_key as "codigo"
        //     from users as u, user_data as data1, user_data as data2
        //     where data1.field_key = "telefono"
        //     and data2.field_key = "codigo"
        //     and u.rol_id = "'.$rol.'"
        //     #GROUP BY "telefono"
        // ');

        return $userdata;
    }

    /*
     * buscador administradores
     */
    public function searchAdmin($name = null){
        if($name){
            //$admin = DB::table('users')->where('name','like','%'. $name .'%')->get();

            // $admin = DB::table('users')->join('user_data','users.id', '=','user_data.user_id' )
            //                            ->select('users.*','user_data.field_key','user_data.value_key')
            //                            ->get();
           

        } else {
            //$admin = User::where('rol_id',1)->get();
            $admin = DB::table('users')->join('user_data','users.id', '=','user_data.user_id' )
                                       ->select('users.*','user_data.field_key','user_data.value_key')
                                       ->where('field_key','telefono')
                                       ->where('rol_id',1)
                                       ->get();
        }
        return $admin;
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

        // return response()->json([
        //     'rol' => $user[0]->rol_id,
        //     'nombres' => $metadata[0]->value_key,
        //     'apellidos' => $metadata[1]->value_key,
        //     'telefono' => $metadata[2]->value_key,
        //     'email' => $user[0]->email,
        //     'ciudad' => $metadata[4]->value_key,
        // ]);

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
            // 'field_key' => $request->name,
            'campo_id' => $request->name,
            'value_key' => $request->nombre,
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

    public function assignedCustomers($id){

        // $idAsig = intval($id);

        // // $assignedCustomers = VendedorCliente::where('vendedor_id',$id)->get();
        // // return $assignedCustomers;

        // // $assignedCustomers = User::find($id)->vendedores()->get();
        // dd($idAsig);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

    
    public function store(UserRequest $request)
    // public function store(Request $request)
    {
        // return $validate;
        // $request->validate([
        //     'rol_id'   => 'required',
        //     'name'     => 'required|string',
        //     'email'    => 'required|string|email|unique:users',
        //     'password' => 'required|string',
        // ]);
        
        $validate = $request->validated();
        $user = User::create([
            'rol_id' => $request->rol_id,
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password)
        ]);

        $metadata = $request->userdata;
        if($metadata != null){
            foreach($metadata as $key => $value){
                $metadata = UserData::create([
                    'user_id' => $user->id,
                    'field_key' => $key,
                    'value_key' => $value
                ]);
            }
        }

        return response()->json([
            'tmp_user' => $user->id,
            'message' => 'Successfully created user!'
        ], 201);
    }

    public function userData(Request $request){

        return $request;

        $metadata[] = $request;
        if($metadata != null){
            foreach($metadata as $key => $value){
                $metadata[] = UserData::create([
                    'user_id' => $request->user_id,
                    'field_key' => $key,
                    'value_key' => $value
                ]);
            }
        }

        return response()->json([
            'message' => 'Successfully created user!'
        ], 201);

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
    // public function destroy(User $user)
    public function destroy(User $user)
    {
        // return $user;
        $user->delete();
        return $user;
    }

    public function destroyUsers(Request $request)
    {
        $data = $request->all();
        
        for($i = 0; $i < count($data); $i++ ){
            User::where('id',$data[$i])->delete();
        }
        return response()->json([
            'messages' => 'Datos eliminados',
        ], 201);

    }

}
