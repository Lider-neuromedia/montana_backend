<?php

namespace App\Http\Controllers;

use App\Entities\Rol;
use App\Entities\SeguimientoPqrs;
use App\Entities\Tienda;
use App\Entities\User;
use App\Entities\UserData;
use App\Entities\Valoracion;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function roles()
    {
        $roles = Rol::all();
        return response()->json($roles, 200);
    }

    public function index()
    {
        $users = User::paginate(20);
        return response()->json($users, 200);
    }

    public function usuariosPorRol(Request $request, $rol_id)
    {
        $users = User::query()
            ->where('rol_id', $rol_id)
            ->orderBy('name', 'asc')
            ->orderBy('apellidos', 'asc')
            ->paginate(20);
        return response()->json($users, 200);
    }

    private function buscarUsuariosPorRol($rol_id, $search, $es_simple = false)
    {
        $users = User::query()
            ->when($search, function ($q) use ($search) {
                $q->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('apellidos', 'like', "%{$search}%")
                        ->orWhere('dni', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->where('rol_id', $rol_id)
            ->when($es_simple == false, function ($q) use ($rol_id) {
                $q->with('datos');
            })
            ->paginate(20);

        return $users;
    }

    /**
     * Obtener los campos extra que tiene un rol de usuario;
     */
    private function camposExtraPorRol($rol_id)
    {
        return UserData::query()
            ->whereHas('usuario', function ($q) use ($rol_id) {
                $q->where('rol_id', $rol_id);
            })
            ->get()
            ->pluck('field_key')
            ->unique()
            ->flatten();
    }

    public function store(Request $request)
    {
        $request->validate([
            'rol_id' => ['required', 'integer', 'exists:roles,id'],
            'name' => ['required', 'string', 'max:250'],
            'apellidos' => ['required', 'string', 'max:250'],
            'email' => ['required', 'string', 'email:rfc,dns', 'unique:users,email', 'max:100'],
            'tipo_identificacion' => ['required', 'string', 'max:250'],
            'dni' => ['required', 'string', 'max:250'],
            'password' => ['required', 'string', 'min:8', 'max:50'],

            'datos' => ['required', 'array', 'min:1'],
            'datos.*.field_key' => ['required', 'string', 'max:250'],
            'datos.*.value_key' => ['required', 'string', 'max:250'],

            'clientes' => ['required_if:rol_id,2', 'array', 'min:1'],
            'clientes.*.cliente_id' => ['required', 'integer', 'exists:users,id'],
            'clientes.*.tienda_id' => ['required', 'integer', 'exists:tiendas,id_tiendas'],

            'tiendas' => ['required_if:rol_id,3', 'array', 'min:1'],
            'tiendas.*.nombre' => ['required', 'string', 'max:60'],
            'tiendas.*.lugar' => ['required', 'string', 'max:40'],
            'tiendas.*.local' => ['nullable', 'string', 'max:30'],
            'tiendas.*.direccion' => ['nullable', 'string', 'max:60'],
            'tiendas.*.telefono' => ['nullable', 'string', 'max:40'],
            'tiendas.*.sucursal' => ['nullable', 'string', 'max:1'],
            'tiendas.*.fecha_ingreso' => ['required', 'date_format:Y-m-d'],
            'tiendas.*.fecha_ultima_compra' => ['required', 'date_format:Y-m-d'],
            'tiendas.*.cupo' => ['required', 'numeric', 'min:0'],
            'tiendas.*.ciudad_codigo' => ['required', 'numeric'],
            'tiendas.*.zona' => ['nullable', 'numeric', 'min:0'],
            'tiendas.*.bloqueado' => ['required', 'string', 'in:N,S'],
            'tiendas.*.bloqueado_fecha' => ['nullable', 'date_format:Y-m-d'],
            'tiendas.*.nombre_representante' => ['nullable', 'string', 'max:80'],
            'tiendas.*.plazo' => ['required', 'integer', 'min:0'],
            'tiendas.*.escala_factura' => ['nullable', 'string', 'max:1'],
            'tiendas.*.observaciones' => ['nullable', 'string', 'max:2000'],
            'tiendas.*.vendedores' => ['required', 'array', 'min:1'],
            'tiendas.*.vendedores.*' => ['required', 'integer', 'exists:users,id'],
        ]);

        try {

            \DB::beginTransaction();

            // Informacion basica del usuario.
            $rol = $request->get('rol_id');
            $userData = $request->only('rol_id', 'name', 'apellidos', 'tipo_identificacion', 'dni', 'email');
            $userData['password'] = bcrypt($request->get('password'));

            $user = User::create($userData);

            // Datos extra.
            $datos = $request->get('datos') ?: [];

            foreach ($datos as $data) {
                $user->datos()->save(new UserData($data));
            }

            if ($rol == 2) {
                // Vendedor. Asignar clientes.
                $clientes = $request->get('clientes') ?: [];

                foreach ($clientes as $cliente) {
                    $user->clientes()->syncWithoutDetaching([
                        $cliente['cliente_id'],
                    ]);
                    $user->vendedorTiendas()->syncWithoutDetaching([
                        $cliente['tienda_id'],
                    ]);
                }
            } else if ($rol == 3) {
                // Cliente. Asignar tiendas y vendedores.
                $tiendas = $request->get('tiendas') ?: [];

                foreach ($tiendas as $tiendaData) {
                    $tienda = new Tienda([
                        'nombre' => $tiendaData['nombre'],
                        'lugar' => $tiendaData['lugar'],
                        'local' => $tiendaData['local'],
                        'direccion' => $tiendaData['direccion'],
                        'telefono' => $tiendaData['direccion'],
                        'sucursal' => $tiendaData['sucursal'],
                        'fecha_ingreso' => $tiendaData['fecha_ingreso'],
                        'fecha_ultima_compra' => $tiendaData['fecha_ultima_compra'],
                        'cupo' => $tiendaData['cupo'],
                        'ciudad_codigo' => $tiendaData['ciudad_codigo'],
                        'zona' => $tiendaData['zona'],
                        'bloqueado' => $tiendaData['bloqueado'],
                        'bloqueado_fecha' => $tiendaData['bloqueado_fecha'],
                        'nombre_representante' => $tiendaData['nombre_representante'],
                        'plazo' => $tiendaData['plazo'],
                        'escala_factura' => $tiendaData['escala_factura'],
                        'observaciones' => $tiendaData['observaciones'],
                    ]);
                    $tienda->propietario()->associate($user);
                    $tienda->save();

                    $vendedores_ids = $tiendaData['vendedores'] ?: [];
                    $tienda->vendedores()->syncWithoutDetaching($vendedores_ids);
                    $user->vendedores()->syncWithoutDetaching($vendedores_ids);
                }
            }

            \DB::commit();

            return response()->json([
                'response' => 'success',
                'status' => 200,
                'message' => 'Usuario creado de manera correcta!',
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

    public function actualizarUsuario(Request $request, $id)
    {
        $userId = $id;
        $request->validate([
            'rol_id' => ['required', 'integer', 'exists:roles,id'],
            'name' => ['required', 'string', 'max:250'],
            'apellidos' => ['required', 'string', 'max:250'],
            'dni' => ['required', 'string', 'max:250'],
            'email' => ['required', 'string', 'email:rfc,dns', "unique:users,email,$userId", 'max:100'],
            'tipo_identificacion' => ['required', 'string', 'max:250'],
            'password' => ['nullable', 'string', 'min:8', 'max:50'],

            'datos' => ['required', 'array', 'min:1'],
            'datos.*.field_key' => ['required', 'string', 'max:250'],
            'datos.*.value_key' => ['required', 'string', 'max:250'],

            'clientes' => ['required_if:rol_id,2', 'array', 'min:1'],
            'clientes.*.cliente_id' => ['required', 'integer', 'exists:users,id'],
            'clientes.*.tienda_id' => ['required', 'integer', 'exists:tiendas,id_tiendas'],

            'tiendas' => ['required_if:rol_id,3', 'array', 'min:1'],
            'tiendas.*.id' => ['nullable', 'integer', 'exists:tiendas,id_tiendas'],
            'tiendas.*.nombre' => ['required', 'string', 'max:60'],
            'tiendas.*.lugar' => ['required', 'string', 'max:40'],
            'tiendas.*.local' => ['nullable', 'string', 'max:30'],
            'tiendas.*.direccion' => ['nullable', 'string', 'max:60'],
            'tiendas.*.telefono' => ['nullable', 'string', 'max:40'],
            'tiendas.*.sucursal' => ['nullable', 'string', 'max:1'],
            'tiendas.*.fecha_ingreso' => ['required', 'date_format:Y-m-d'],
            'tiendas.*.fecha_ultima_compra' => ['required', 'date_format:Y-m-d'],
            'tiendas.*.cupo' => ['required', 'numeric', 'min:0'],
            'tiendas.*.ciudad_codigo' => ['required', 'numeric'],
            'tiendas.*.zona' => ['nullable', 'numeric', 'min:0'],
            'tiendas.*.bloqueado' => ['required', 'string', 'in:N,S'],
            'tiendas.*.bloqueado_fecha' => ['nullable', 'date_format:Y-m-d'],
            'tiendas.*.nombre_representante' => ['nullable', 'string', 'max:80'],
            'tiendas.*.plazo' => ['required', 'integer', 'min:0'],
            'tiendas.*.escala_factura' => ['nullable', 'string', 'max:1'],
            'tiendas.*.observaciones' => ['nullable', 'string', 'max:2000'],
            'tiendas.*.vendedores' => ['required', 'array', 'min:1'],
            'tiendas.*.vendedores.*' => ['required', 'integer', 'exists:users,id'],
        ]);

        try {

            \DB::beginTransaction();

            // Actualizacion de la informacion basica del usuario.
            $rol = $request->get('rol_id');
            $userData = $request->only('rol_id', 'name', 'apellidos', 'tipo_identificacion', 'dni', 'email');

            if ($request->has('password') && $request->get('password')) {
                $userData['password'] = bcrypt($request->get('password'));
            }

            $user = User::findOrFail($userId);
            $user->update($userData);

            // Datos extra.
            $datos = $request->get('datos') ?: [];

            foreach ($datos as $data) {
                $usuarioData = $user->datos()
                    ->where('field_key', $data['field_key'])
                    ->first();

                if ($usuarioData != null) {
                    $usuarioData->update([
                        'field_key' => $data['field_key'],
                        'value_key' => $data['value_key'],
                    ]);
                } else {
                    $user->datos()->save(new UserData([
                        'field_key' => $data['field_key'],
                        'value_key' => $data['value_key'],
                    ]));
                }
            }

            if ($rol == 2) {
                // Vendedor. Asignar clientes y tiendas.
                $clientes = $request->get('clientes') ?: [];
                $clientes_ids = [];
                $tiendas_ids = [];

                foreach ($clientes as $cliente) {
                    $clientes_ids[] = $cliente['cliente_id'];
                    $tiendas_ids[] = $cliente['tienda_id'];
                }

                $user->clientes()->detach();
                $user->clientes()->syncWithoutDetaching(
                    array_unique($clientes_ids));

                $user->vendedorTiendas()->detach();
                $user->vendedorTiendas()->syncWithoutDetaching(
                    array_unique($tiendas_ids));
            } else if ($rol == 3) {
                // Cliente. Asignar tiendas y vendedores.
                $tiendas = $request->get('tiendas') ?: [];
                $vendedores_all_ids = [];

                foreach ($tiendas as $tiendaData) {
                    $tienda = isset($tiendaData['id']) ? $user->tiendas()->find($tiendaData['id']) : null;
                    $tData = [
                        'nombre' => $tiendaData['nombre'],
                        'lugar' => $tiendaData['lugar'],
                        'local' => $tiendaData['local'],
                        'direccion' => $tiendaData['direccion'],
                        'telefono' => $tiendaData['direccion'],
                        'sucursal' => $tiendaData['sucursal'],
                        'fecha_ingreso' => $tiendaData['fecha_ingreso'],
                        'fecha_ultima_compra' => $tiendaData['fecha_ultima_compra'],
                        'cupo' => $tiendaData['cupo'],
                        'ciudad_codigo' => $tiendaData['ciudad_codigo'],
                        'zona' => $tiendaData['zona'],
                        'bloqueado' => $tiendaData['bloqueado'],
                        'bloqueado_fecha' => $tiendaData['bloqueado_fecha'],
                        'nombre_representante' => $tiendaData['nombre_representante'],
                        'plazo' => $tiendaData['plazo'],
                        'escala_factura' => $tiendaData['escala_factura'],
                        'observaciones' => $tiendaData['observaciones'],
                    ];

                    if ($tienda != null) {
                        $tienda->update($tData);
                    } else {
                        $tienda = new Tienda($tData);
                        $tienda->propietario()->associate($user);
                        $tienda->save();
                    }

                    // Asignar vendedores a tienda.
                    $vendedores_ids = $tiendaData['vendedores'] ?: [];
                    $vendedores_all_ids = array_merge(
                        $vendedores_all_ids,
                        $vendedores_ids);

                    $tienda->vendedores()->detach();
                    $tienda->vendedores()->syncWithoutDetaching(
                        array_unique($vendedores_ids));
                }

                // Asignar vendedores a cliente.
                $user->vendedores()->detach();
                $user->vendedores()->syncWithoutDetaching(
                    array_unique($vendedores_all_ids));
            }

            \DB::commit();

            return response()->json([
                'response' => 'success',
                'message' => 'Usuario actualizado con exito.',
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

    public function eliminarUsuarios(Request $request)
    {
        $request->validate([
            'usuarios' => ['required', 'array', 'min:1'],
            'usuarios.*' => ['required', 'integer', 'exists:users,id'],
        ]);

        try {

            \DB::beginTransaction();

            foreach ($request->get('usuarios') as $id) {
                $user = User::findOrFail($id);

                // Validar PQRS.
                $validate_pqrs = SeguimientoPqrs::where('usuario', $id)->exists();

                if ($validate_pqrs) {
                    throw new \Exception("El usuario {$user->nombre_completo} no se puede eliminar, porque tiene pqrs/mensajes asignados.", 403);
                }

                // Validar clientes y pedidos de vendedor.
                $validate_vendedor_clientes = $user->clientes()->count() > 0;
                $validate_vendedor_pedidos = $user->vendedorPedidos()->count() > 0;

                if ($validate_vendedor_clientes) {
                    throw new \Exception("El usuario {$user->nombre_completo} no se puede eliminar, porque tiene clientes asignados.", 403);
                }
                if ($validate_vendedor_pedidos) {
                    throw new \Exception("El usuario {$user->nombre_completo} no se puede eliminar, porque tiene pedidos registrados.", 403);
                }

                // Validar vendedores y pedidos de cliente.
                $validate_cliente_vendedores = $user->vendedores()->count() > 0;
                $validate_cliente_pedidos = $user->clientePedidos()->count() > 0;

                if ($validate_cliente_vendedores) {
                    throw new \Exception("El usuario {$user->nombre_completo} no se puede eliminar, porque tiene vendedores asignados.", 403);
                }
                if ($validate_cliente_pedidos) {
                    throw new \Exception("El usuario {$user->nombre_completo} no se puede eliminar, porque tiene pedidos registrados.", 403);
                }

                // Borrar.
                \DB::table('vendedor_cliente')->where('cliente', $id)->delete();
                Tienda::where('cliente', $id)->delete();
                UserData::where('user_id', $id)->delete();
                Valoracion::where('usuario', $id)->delete();
                $user->delete();
            }

            \DB::commit();

            return response()->json([
                'response' => 'success',
                'message' => "Usuarios eliminados correctamente",
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

    // ADMINISTRADORES -------------------------------------------

    public function administradores(Request $request)
    {
        $search = $request->get('search') ?: null;
        $fields = $this->camposExtraPorRol(1);
        $users = $this->buscarUsuariosPorRol(1, $search);
        return response()->json(compact('fields', 'users'), 200);
    }

    public function administrador(Request $request, $id)
    {
        $admin = User::query()
            ->where('id', $id)
            ->where('rol_id', 1)
            ->with('datos')
            ->firstOrFail();
        return response()->json($admin, 200);
    }

    // VENDEDORES -------------------------------------------

    public function vendedores(Request $request)
    {
        $search = $request->get('search') ?: null;
        $fields = $this->camposExtraPorRol(2);
        $users = $this->buscarUsuariosPorRol(2, $search);
        return response()->json(compact('fields', 'users'), 200);
    }

    public function buscarVendedor(Request $request)
    {
        $search = $request->get('search') ?: false;
        $vendedores = $this->buscarUsuariosPorRol(2, $search, true);

        return response()->json([
            'response' => 'success',
            'vendedores' => $vendedores,
            'status' => 200,
        ], 200);
    }

    public function vendedor($id)
    {
        $vendedor = User::query()
            ->where('rol_id', 2)
            ->where('id', $id)
            ->with('datos')
            ->firstOrFail();

        return response()->json($vendedor, 200);
    }

    public function clientesAsignados($vendedor_id)
    {
        $clientes = User::query()
            ->where('rol_id', 3)
            ->whereHas('vendedores', function ($q) use ($vendedor_id) {
                $q->where('id', $vendedor_id);
            })
            ->with('datos')
            ->get();

        return response()->json($clientes, 200);
    }

    // CLIENTES -------------------------------------------

    public function clientes(Request $request)
    {
        $search = $request->get('search') ?: null;
        $fields = $this->camposExtraPorRol(3);
        $users = $this->buscarUsuariosPorRol(3, $search);
        return response()->json(compact('fields', 'users'), 200);
    }

    public function buscarClientes(Request $request)
    {
        $search = $request->get('search') ?: false;
        $clientes = $this->buscarUsuariosPorRol(3, $search, true);

        return response()->json([
            'response' => 'success',
            'clientes' => $clientes,
            'status' => 200,
        ], 200);
    }

    public function cliente($id)
    {
        $cliente = User::query()
            ->where('rol_id', 3)
            ->where('id', $id)
            ->with('datos')
            ->firstOrFail();

        return response()->json($cliente, 200);
    }

    public function vendedoresAsignados($cliente_id)
    {
        $vendedores = User::query()
            ->where('rol_id', 2)
            ->whereHas('clientes', function ($q) use ($cliente_id) {
                $q->where('id', $cliente_id);
            })
            ->with('datos')
            ->get();

        return response()->json($vendedores, 200);
    }

    // CLIENTES -------------------------------------------

    public function asignarVendedorTienda($vendedor_id, $tienda_id)
    {
        try {

            \DB::beginTransaction();

            $vendedor = User::findOrFail($vendedor_id);
            $tienda = Tienda::findOrFail($tienda_id);

            $tienda->vendedores()
                ->syncWithoutDetaching([$vendedor->id]);

            $tienda->propietario->vendedores()
                ->syncWithoutDetaching([$vendedor->id]);

            \DB::commit();

            return response()->json([
                'response' => 'success',
                'message' => 'Cliente/Tienda asignado a vendedor correctamente.',
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

    public function quitarVendedorTienda($vendedor_id, $tienda_id)
    {
        try {

            \DB::beginTransaction();

            $vendedor = User::findOrFail($vendedor_id);
            $tienda = Tienda::findOrFail($tienda_id);

            $tienda->vendedores()
                ->detach($vendedor->id);

            $tienda->propietario->vendedores()
                ->detach($vendedor->id);

            \DB::commit();

            return response()->json([
                'response' => 'success',
                'message' => 'Cliente/Tienda retirado de vendedor correctamente.',
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
}
