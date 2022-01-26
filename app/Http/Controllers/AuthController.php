<?php

namespace App\Http\Controllers;

use App\Entities\User;
use App\Entities\UserData;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function signup(Request $request)
    {
        $request->validate([
            'rol_id' => 'required',
            'name' => 'required|string',
            'email' => 'required|string|email|unique:users',
            'password' => 'required|string|confirmed',
        ]);
        $user = new User([
            'rol_id' => $request->rol_id,
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
        ]);
        $user->save();

        return response()->json([
            'message' => 'Successfully created user!',
        ], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
            'remember_me' => 'boolean',
        ]);

        $credentials = $request->only('email', 'password');

        if (!Auth::attempt($credentials)) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 401);
        }

        $user = auth()->user();
        $userdata = UserData::where('user_id', $user->id)->get();

        if ($user->rol_id == 1) {
            $accesos = ['all'];
        } else if ($user->rol_id == 2) {
            $accesos = ['catalogos', 'pedidos', 'showRoom', 'clientes', 'pqrs', 'ampliacion_cupo'];
        } else if ($user->rol_id == 3) {
            $accesos = [];
        }

        $tokenResult = $user->createToken('Personal Access Token');
        $token = $tokenResult->token;

        if ($request->remember_me) {
            $token->expires_at = Carbon::now()->addWeeks(1);
        }

        $token->save();

        return response()->json([
            'access_token' => $tokenResult->accessToken,
            'token_type' => 'Bearer',
            'expires_at' => Carbon::parse($tokenResult->token->expires_at)->toDateTimeString(),
            'id' => $user->id,
            'email' => $user->email,
            'name' => $user->name,
            'apellidos' => $user->apellidos,
            'rol' => $user->rol_id,
            'dni' => $user->dni,
            'tipo_identificacion' => $user->tipo_identificacion,
            'userdata' => $userdata,
            'permisos' => $accesos,
        ], 200);
    }

    public function logout(Request $request)
    {
        auth()->user()->update(['device_token' => null]);
        $request->user()->token()->revoke();
        return response()->json(['message' => 'Successfully logged out'], 200);
    }

    public function user(Request $request)
    {
        return response()->json($request->user(), 200);
    }

    public function getUserSesion()
    {
        if (auth()->user() != null) {
            $user = auth()->user();
            $userdata = UserData::where('user_id', $user->id)->get();

            if ($user->rol_id == 1) {
                $accesos = ['all'];
            } else if ($user->rol_id == 2) {
                $accesos = ['catalogos', 'pedidos', 'showRoom', 'clientes', 'pqrs', 'ampliacion_cupo'];
            } else if ($user->rol_id == 3) {
                $accesos = [];
            }

            return response()->json([
                'response' => 'success',
                'status' => 200,
                'user' => $user,
                'userdata' => $userdata,
                'accesos' => $accesos,
            ], 200);
        }

        return response()->json([
            'response' => 'error',
            'status' => 403,
            'message' => 'Token vencido o invalido.',
        ], 403);
    }

    public function dashboardResumen(Request $request)
    {
        $request->validate([
            'fecha_atendidos' => ["nullable", "date_format:Y-m"],
        ]);

        $user = auth()->user();

        if ($user->rol_id == 2) { // Vendedor
            $fecha_atendidos = $request->get('fecha_atendidos');

            // Cantidad de clientes
            $cantidad_clientes = \DB::table('vendedor_cliente')
                ->select(\DB::raw('count(*) as clientes'))
                ->where('vendedor', $user->id)
                ->count();

            // Cantidad de clientes atendidos
            $cantidad_clientes_atendidos = \DB::table('pqrs')
                ->where('estado', 'cerrado')
                ->where('vendedor', $user->id)
                ->when($fecha_atendidos, function ($q) use ($fecha_atendidos) {
                    $fa = Carbon::createFromFormat('Y-m', $fecha_atendidos);
                    $q->whereMonth('created_at', $fa)->whereYear('created_at', $fa);
                })
                ->groupBy('cliente')
                ->count();

            $cantidad_pedidos = $this->calcularCantidadPedidos($user);

            return response()->json(compact(
                'cantidad_clientes',
                'cantidad_clientes_atendidos',
                'cantidad_pedidos',
            ), 200);
        }

        if ($user->rol_id == 3) { // Cliente
            // Tiendas Creadas
            $cantidad_tiendas = \DB::table('tiendas')
                ->where('cliente', $user->id)
                ->count();

            // PQRS generados
            $cantidad_pqrs = \DB::table('pqrs')
                ->where('cliente', $user->id)
                ->count();

            $cantidad_pedidos = $this->calcularCantidadPedidos($user);

            return response()->json(compact(
                'cantidad_tiendas',
                'cantidad_pqrs',
                'cantidad_pedidos'
            ), 200);
        }

        return response()->json([], 200);
    }

    public function calcularCantidadPedidos(User $user)
    {
        $cantidad_pedidos = \DB::table('pedidos')
            ->select('estado', \DB::raw('count(*) as cantidad'))
            ->when($user->rol_id == 2, function ($q) use ($user) { // Vendedor
                $q->where('vendedor', $user->id);
            })
            ->when($user->rol_id == 3, function ($q) use ($user) { // Cliente
                $q->where('cliente', $user->id);
            })
            ->groupBy('estado')
            ->get();

        $aprobados = 0;
        $rechazados = 0;
        $pendientes = 0;
        $realizados = 0;

        foreach ($cantidad_pedidos as $cp) {
            $aprobados += $cp->cantidad;

            if ($cp->estado == 1) {
                $realizados = $cp->cantidad;
            }
            if ($cp->estado == 2) {
                $pendientes = $cp->cantidad;
            }
            if ($cp->estado == 3) {
                $rechazados = $cp->cantidad;
            }
        }

        return [
            'realizados' => $aprobados,
            'aprobados' => $rechazados,
            'rechazados' => $pendientes,
            'pendientes' => $realizados,
        ];
    }
}
