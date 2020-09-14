<?php

namespace App\Http\Controllers;

use App\Entities\User;
use App\Entities\UserData;
use App\Entities\VendedorCliente;
use Illuminate\Http\Request;

use DB;

use App\Http\Requests\UserRequest;

// use App\Entities\Rol;
use Illuminate\Support\Collection;
use Illuminate\Support\Arr;
use Auth;

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
    // public function searchAdmin($name = null){
    //     if($name){
    //         //$admin = DB::table('users')->where('name','like','%'. $name .'%')->get();

    //         // $admin = DB::table('users')->join('user_data','users.id', '=','user_data.user_id' )
    //         //                            ->select('users.*','user_data.field_key','user_data.value_key')
    //         //                            ->get();
           

    //     } else {
    //         //$admin = User::where('rol_id',1)->get();
    //         $admin = DB::table('users')->join('user_data','users.id', '=','user_data.user_id' )
    //                                    ->select('users.*','user_data.field_key','user_data.value_key')
    //                                    ->where('field_key','telefono')
    //                                    ->where('rol_id',1)
    //                                    ->get();
    //     }
    //     return $admin;
    // }
    public function getAdmins(){
        $admins = DB::table('users')->where('rol_id', 1)->get();
        foreach ($admins as $admin) {
            $data_admin = DB::table('user_data')->where('user_id', $admin->id)->get();
            $admin->user_data = $data_admin;
        }
        
        $admin_ramdom = DB::table('users')->where('rol_id', 1)->first();
        $fields_db = DB::table('user_data')->where('user_id', $admin_ramdom->id)->get();
        $fields = [];
        foreach ($fields_db as  $field) {
            $fields[] = $field->field_key;
        }
        $response = ['fields' => $fields, 'admins' => $admins];
        return response()->json($response);
    }

    public function getAdmin($id){
        $admin = DB::table('users')->where('id', $id)->where('rol_id', 1)->first();
        if ($admin == null) {
            $response = [
                'response' => 'error',
                'message' => 'El id del usuario no es un administrador.',
                'status' => 403
            ];
            return response()->json($response);
        }

        $data_admin = DB::table('user_data')->where('user_id', $id)->get();
        $admin->user_data = $data_admin;
    
        return response()->json($admin);
    }

    public function getVendedores(){
        $userdata = User::where('rol_id',2)->get();
        return $userdata;
    }

    public function getClientes(){
        $userdata = User::where('rol_id', 3)->get();
        return $userdata;
    }

    public function getVendedor($id){
        // Buscar el vendedor.
        $user = User::where('rol_id', 2)->find($id);
        // validar que sea un vendedor.
        if ($user == null) {
            $response = [
                'response' => 'error',
                'message' => 'El id del usuario no es un vendedor.',
                'status' => 403
            ];
            return response()->json($response);
        }

        // Buscar la data del usuario en cuestion.
        $metadata = UserData::where('user_id', $id)->get();

        // Organizar los campos administrados del usuario y organizarlos.
        $filterData = [];
        foreach($metadata as $mt){
            $filterData[] = [
                'id_field' => $mt->id,
                'field_key' => $mt->field_key,
                'value_key' => $mt->value_key
            ];
        }
        // Setear los campos administrador en un atributo "data_user".
        $user->data_user = $filterData;
        // Buscar clientes.
        $clientes_vendedor = DB::table('vendedor_cliente')
                            ->select('id_vendedor_cliente', 'id as id_cliente', 'rol_id', 'name', 'email')
                            ->join('users', 'cliente', '=', 'id')
                            ->where('vendedor', $id)
                            ->get();
        
        // Setear clientes.
        $user->clientes = $clientes_vendedor;
        return response()->json(
            $user
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
        
        // Buscar el cliente.
        $user = User::where('rol_id', 3)->find($id);
        // validar que sea un cliente.
        if ($user == null) {
            $response = [
                'response' => 'error',
                'message' => 'El id del usuario no es un cliente.',
                'status' => 403
            ];
            return response()->json($response);
        }

        // Buscar la data del usuario en cuestion.
        $metadata = UserData::where('user_id', $id)->get();

        // Organizar los campos administrados del usuario y organizarlos.
        $filterData = [];
        foreach($metadata as $mt){
            $filterData[] = [
                'id_field' => $mt->id,
                'field_key' => $mt->field_key,
                'value_key' => $mt->value_key
            ];
        }
        // Setear los campos administrador en un atributo "data_user".
        $user->data_user = $filterData;

        // Buscar su vendedor.
        $vendedor = DB::table('vendedor_cliente')
                            ->select('id_vendedor_cliente', 'id as id_cliente', 'rol_id', 'name', 'email')
                            ->join('users', 'vendedor', '=', 'id')
                            ->where('cliente', $id)
                            ->first();
        // Setear el vendedor.
        $user->vendedor = $vendedor;

        // Buscar las tiendas del cliente.
        $tiendas = DB::table('tiendas')
                    ->select('id_tiendas', 'nombre', 'lugar', 'local', 'codigo', 'direccion', 'telefono', 'ciudad', 'nombre_ciudad')
                    ->join('ciudades', 'ciudad', '=', 'id_ciudad')
                    ->where('cliente', $id)
                    ->get();
        $user->tiendas = $tiendas;
        
         return response()->json(
            $user
         );

    }

    public function createAdmin(Request $request){

        $request->validate([
            'rol_id'   => 'required',
            'name'     => 'required|string',
            'apellidos'     => 'required|string',
            'email'    => 'required|string|email|unique:users',
            'password' => 'required|string',
        ]);

        $user = User::create([
            //'rol_id' => $request->rol,
            'rol_id' => 1,
            'name' => $request->name,
            'apellidos' => $request->apellidos,
            'email' => $request->email,
            'password' => bcrypt($request->password)
        ]);

        $userdata = UserData::create([
            'user_id' => $user->id,
            'field_key' => $request->name,
            'value_key' => $request->nombre,
            // 'campo_id' => $request->name,
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

        $vendedor = DB::table('users')->where('id', $id)->where('rol_id', 2)->first();
        if ($vendedor == null) {
            $response = [
                'response' => 'error',
                'message' => 'El id del usuario no es un vendedor.',
                'status' => 403
            ];
            return response()->json($response);
        }

        $clientes_vendedor = DB::table('vendedor_cliente')
        ->select('id_vendedor_cliente', 'id as id_cliente', 'rol_id', 'name', 'email')
        ->join('users', 'cliente', '=', 'id')
        ->where('vendedor', $id)
        ->get();

        foreach ($clientes_vendedor as $cliente) {
            // Buscar la data del usuario en cuestion.
            $metadata = UserData::where('user_id', $cliente->id_cliente)->get();
            // Organizar los campos administrados del usuario y organizarlos.
            $filterData = [];
            foreach($metadata as $mt){
                $filterData[] = [
                    'id_field' => $mt->id,
                    'field_key' => $mt->field_key,
                    'value_key' => $mt->value_key
                ];
            }
            $cliente->data = $filterData;
        }

        return response()->json($clientes_vendedor);
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
            'apellidos' => $request->apellidos,
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
    public function updateUser(Request $request){
        $request->validate([
            'id' => 'required',
            'rol_id' => 'required',
            'name' => 'required',
            'email' => 'required',
            'user_data' => 'required'
        ]);

        //Actualizacion de la informacion basica del usuario.
        $user = User::find($request['id']); 
        $user->rol_id = $request['rol_id'];
        $user->name = $request['name'];
        $user->email = $request['email'];

        if(!$user->save()){
            $response = [
                'response' => 'error',
                'message' => 'Error en la actualizacion del usuario.',
                'status' => 403
            ];
            return response()->json($response);
        }

        foreach ($request['user_data'] as $data) {
            $user_data = UserData::find($data['id_field']);
            $user_data->field_key = $data['field_key'];
            $user_data->value_key = $data['value_key'];
            $user_data->save();
        }

        $response = [
            'response' => 'success',
            'message' => 'Usuario actualizado con exito.',
            'status' => 200
        ];

        return response()->json($response);
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
        $data = $request->get('id');
        $res = User::whereIn('id', explode(',', $data))->get();
        dd($res);
        // $result=myModel::whereIn('id',$id)->delete();

        // for($i = 0; $i < count($data); $i++ ){
        //     User::where('id',$data[$i])->delete();
        // }
        // return response()->json([
        //     'messages' => 'Datos eliminados',
        // ], 201);

    }

}
