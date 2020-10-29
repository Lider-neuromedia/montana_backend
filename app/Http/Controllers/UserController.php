<?php

namespace App\Http\Controllers;

use App\Entities\Pedido;
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

    
    public function getUsers($rol_id){
        $users = DB::table('users')->where('rol_id', $rol_id)->get();

        foreach ($users as $user) {
            $data_admin = DB::table('user_data')->where('user_id', $user->id)->get();
            $user->user_data = $data_admin;
            $user->iniciales = substr($user->name, 0, 1);
            $user->iniciales .= substr($user->apellidos, 0, 1);
            if ($rol_id == 3) {
                $vendedor = DB::table('vendedor_cliente')
                            ->where('cliente', $user->id)
                            ->join('users', 'vendedor', '=', 'users.id')
                            ->first();
                $user->vendedor = $vendedor;
            }
        }
        
        $admin_ramdom = DB::table('users')->where('rol_id', $rol_id)->first();
        $fields_db = DB::table('user_data')->where('user_id', $admin_ramdom->id)->get();
        $fields = [];
        foreach ($fields_db as  $field) {
            $fields[] = $field->field_key;
        }
        $response = ['fields' => $fields, 'users' => $users];
        return response()->json($response);
    }

    public function getAdmins(){
        return $this->getUsers(1);
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
        return $this->getUsers(2);
    }

    public function getClientes(){
        return $this->getUsers(3);
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
                            ->select('id_vendedor_cliente', 'id as id_vendedor', 'cliente as id_cliente', 'rol_id', 'name', 'email')
                            ->join('users', 'vendedor', '=', 'id')
                            ->where('cliente', $id)
                            ->first();
        if ($vendedor != null) {
            $data_vendedor = DB::table('user_data')->where('user_id', $vendedor->id_vendedor)->get();
            $vendedor->user_data = $data_vendedor;
        }else{
            $vendedor->user_data = [];
        }        
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
    public function store(UserRequest $request){
        $request->validate([
            'rol_id'   => 'required',
            'name'     => 'required|string',
            'apellidos' => 'required|string',
            'dni'     => 'required|string',
            'email'    => 'required|string|email|unique:users',
            'password' => 'required|string',
        ]);
        
        $validate = $request->validated();
        $user = User::create([
            'rol_id' => $request->rol_id,
            'name' => $request->name,
            'apellidos' => $request->apellidos,
            'dni' => $request->dni,
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
            'message' => 'Usuario creado de manera correcta!'
        ], 201);
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
            'dni' => 'required',
            'apellidos' => 'required',
            'email' => 'required',
            'user_data' => 'required'
        ]);

        //Actualizacion de la informacion basica del usuario.
        $user = User::find($request['id']); 
        $user->rol_id = $request['rol_id'];
        $user->name = $request['name'];
        $user->dni = $request['dni'];
        $user->apellidos = $request['apellidos'];
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
    public function destroyUsers(Request $request){

        foreach ($request['usuarios'] as $key => $id) {
            $user = User::find($id);

            if ($user->rol_id == 1) {
                DB::table('user_data')->where('user_id', $id)->delete();
                $user->delete();
                $response = [
                    'response' => 'success', 
                    'message' => "Usuario eliminado correctamente", 
                    'status' => 200
                ];

            }else if($user->rol_id == 2){
                $validate_vendedor = DB::table('vendedor_cliente')->where('vendedor', $id)->exists();
                if (!$validate_vendedor) {
                    DB::table('user_data')->where('user_id', $id)->delete();
                    $user->delete();
                    $response = [
                        'response' => 'success', 
                        'message' => "Usuario eliminado correctamente", 
                        'status' => 200
                    ];

                }else{
                    $response = [
                        'response' => 'error', 
                        'message' => "El usuario {$user->name} {$user->apellido} no se puede eliminar, porque tiene clientes asignados.", 
                        'status' => 200
                    ];
                    return response()->json($response);
                }
            }else{
                
                $validate_cliente = DB::table('pedidos')->where('cliente', $id)->exists();
                if (!$validate_cliente) {
                    DB::table('vendedor_cliente')->where('cliente', $id)->delete();
                    DB::table('user_data')->where('user_id', $id)->delete();
                    $user->delete();
                    $response = [
                        'response' => 'success', 
                        'message' => "Usuario eliminado correctamente", 
                        'status' => 200
                    ];
                }else{
                    $response = [
                        'response' => 'error', 
                        'message' => "El usuario {$user->name} {$user->apellido} no se puede eliminar, porque tiene pedidos registrados.", 
                        'status' => 200
                    ];
                    return response()->json($response);
                }
            }
        }

        return response()->json($response);
    }

    public function searchVendedor(Request $request){
        if (isset($request['search'])) {
            $search = $request['search'];
            if ($request['search'] == '') {
                $vendedores = User::where('rol_id', 2)->get();
            }else{
                $vendedores = User::where( function($query) use($search){
                                $query->where('name', 'like', "%{$search}%");
                                $query->orWhere('apellidos', 'like', "%{$search}%");
                                $query->orWhere('dni', 'like', "%{$search}%");
                            })->where('rol_id', '=' ,2)
                            ->get();
            }
            $response = [
                'response' => 'success',
                'vendedores' => $vendedores,
                'status' => 200
            ];
        }else{
            $response = [
                'response' => 'error',
                'vendedores' => null,
                'status' => 403,
                'message' => 'Sin busqueda.'
            ];
        }

        return response()->json($response);
    }

}
