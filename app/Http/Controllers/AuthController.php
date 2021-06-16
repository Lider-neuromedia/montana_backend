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

        return response()->json(['message' => 'Successfully created user!'], 201);
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
            return response()->json(['message' => 'Unauthorized'], 401);
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

            $response = [
                'response' => 'success',
                'status' => 200,
                'user' => $user,
                'userdata' => $userdata,
                'accesos' => $accesos,
            ];

            return response()->json($response, 200);
        } else {
            $response = [
                'response' => 'error',
                'status' => 403,
                'message' => 'Token vencido o invalido.',
            ];

            return response()->json($response, 403);
        }
    }
}
