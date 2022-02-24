<?php

namespace App\Http\Controllers;

use App\Entities\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function signup(Request $request)
    {
        $request->validate([
            'rol_id' => ['required', 'in:2,3'],
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'string', 'max:100', 'email', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'max:50', 'confirmed'],
        ]);

        $user = new User([
            'rol_id' => $request->get('rol_id'),
            'name' => $request->get('name'),
            'email' => $request->get('email'),
            'password' => bcrypt($request->get('password')),
        ]);
        $user->save();

        return response()->json([
            'message' => 'Usuario creado correctamente.',
        ], 200);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
            'remember_me' => ['boolean'],
        ]);

        $credentials = $request->only('email', 'password');

        if (!Auth::attempt($credentials)) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 401);
        }

        $user = auth()->user();
        $permisos = [];

        if ($user->rol_id == 1) {
            $permisos = [
                'all',
            ];
        } else if ($user->rol_id == 2) {
            $permisos = [
                'catalogos',
                'pedidos',
                'showRoom',
                'clientes',
                'pqrs',
                'ampliacion_cupo',
            ];
        } else if ($user->rol_id == 3) {
            $permisos = [];
        }

        $tokenResult = $user->createToken('Personal Access Token');
        $token = $tokenResult->token;
        $access_token = $tokenResult->accessToken;
        $token_type = 'Bearer';
        $expires_at = Carbon::parse($token->expires_at)->toDateTimeString();

        if ($request->remember_me) {
            $token->expires_at = Carbon::now()->addWeeks(1);
        }

        $token->save();

        return response()->json([
            'access_token' => $access_token,
            'token_type' => $token_type,
            'expires_at' => $expires_at,
            'permisos' => $permisos,
            'id' => $user->id,
            'email' => $user->email,
            'name' => $user->name,
            'apellidos' => $user->apellidos,
            'rol' => $user->rol_id,
            'dni' => $user->dni,
            'tipo_identificacion' => $user->tipo_identificacion,
            'datos' => $user->datos,
        ], 200);
    }

    public function logout(Request $request)
    {
        auth()->user()->update(['device_token' => null]);
        $request->user()->token()->revoke();
        return response()->json(['message' => 'Sesión cerrada correctamente'], 200);
    }

    public function user(Request $request)
    {
        $user = User::query()
            ->where('id', auth()->user()->id)
            ->with('datos')
            ->first();

        return response()->json($user, 200);
    }

    public function getUserSesion()
    {
        $user = auth()->user();
        $user->datos;
        $permisos = [];

        if ($user->rol_id == 1) {
            $permisos = [
                'all',
            ];
        } else if ($user->rol_id == 2) {
            $permisos = [
                'catalogos',
                'pedidos',
                'showRoom',
                'clientes',
                'pqrs',
                'ampliacion_cupo',
            ];
        } else if ($user->rol_id == 3) {
            $permisos = [];
        }

        return response()->json([
            'response' => 'success',
            'status' => 200,
            'user' => $user,
            'permisos' => $permisos,
        ], 200);
    }
}
