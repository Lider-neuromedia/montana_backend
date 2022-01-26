<?php

namespace App\Http\Controllers\Auth;

use App\Entities\User;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class PasswordController extends Controller
{
    const TOKEN_LENGTH = 24;
    const TIME_LIMIT = 30; // Tiempo máximo antes de que expire el token.

    public function sendResetLinkEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email|max:255|exists:users,email',
        ]);

        $to = $request->get('email');
        $token = \Str::random(self::TOKEN_LENGTH);
        $minutes = self::TIME_LIMIT;
        $user = User::where('email', $to)->firstOrFail();
        $nombre = "{$user->name} {$user->apellidos}";

        \DB::table('password_resets')
            ->where('email', $to)
            ->delete();

        \DB::table('password_resets')->insert([
            'email' => $to,
            'token' => \Hash::make($token),
            'created_at' => Carbon::now(),
        ]);

        Mail::send('emails.password', compact('to', 'token', 'minutes', 'nombre'), function ($message) use ($to) {
            $message->to($to)->subject('Athletic Air - Reinicio de Contraseña');
        });

        return response()->json([
            'message' => 'Correo con token de reinicio de contraseña enviado.',
        ], 200);
    }

    public function reset(Request $request)
    {
        $email = $request->get('email');

        $request->validate([
            'email' => ['required', 'string', 'email', 'max:255', 'exists:users,email'],
            'password' => ['required', 'confirmed', 'min:6', 'max:20'],
            'token' => [
                'required',
                'min:' . self::TOKEN_LENGTH,
                'max:' . self::TOKEN_LENGTH,
                function ($attribute, $value, $fail) use ($email) {
                    $row = \DB::table('password_resets')
                        ->where('email', $email)
                        ->first();

                    if (!$row) {
                        $fail('Token inválido.');
                    } else if (Carbon::now()->diffInMinutes($row->created_at) > PasswordController::TIME_LIMIT) {
                        $fail('Token expirado.');
                    } else if (\Hash::check($value, $row->token) == false) {
                        $fail('Token inválido.');
                    }
                },
            ],
        ]);

        try {

            \DB::beginTransaction();

            $password = bcrypt($request->get('password'));
            $user = User::where('email', $email)->firstOrFail();
            $user->update(['password' => $password]);

            \DB::table('password_resets')
                ->where('email', $email)
                ->delete();

            \DB::commit();

            return response()->json(['message' => 'Contraseña actualizada correctamente.'], 200);

        } catch (\Exception $ex) {
            \Log::info($ex->getMessage());
            \Log::info($ex->getTraceAsString());
            \DB::rollBack();
            return response()->json(['message' => $ex->getMessage()], 500);
        }
    }
}
