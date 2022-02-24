<?php

namespace App\Http\Controllers;

use App\Entities\Pqrs;
use App\Entities\SeguimientoPqrs;
use App\Entities\User;
use App\Utils\Notifications;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PqrsController extends Controller
{
    public function index(Request $request)
    {
        $search = $request['search'];
        $user = auth()->user();

        $pqrs = Pqrs::select('id_pqrs', 'codigo', 'fecha_registro', 'ven.name AS name_vendedor',
            'ven.apellidos AS apellidos_vendedor', 'cli.name AS name_cliente', 'cli.apellidos AS apellidos_cliente', 'estado')
            ->join('users AS ven', 'vendedor', '=', 'ven.id')
            ->join('users AS cli', 'cliente', '=', 'cli.id')
            ->when($user->rol_id == 3, function ($q) use ($user) {
                $q->where('cliente', $user->id);
            })
            ->when($user->rol_id == 2, function ($q) use ($user) {
                $q->where('vendedor', $user->id);
            })
            ->where(function ($q) use ($search) {
                $q->where('codigo', 'like', "%$search%")
                    ->orWhere('ven.name', 'like', "%$search%")
                    ->orWhere('ven.apellidos', 'like', "%$search%")
                    ->orWhere('cli.name', 'like', "%$search%")
                    ->orWhere('cli.apellidos', 'like', "%$search%");
            })
            ->get();

        return response()->json([
            'response' => 'success',
            'status' => 200,
            'pqrs' => $pqrs,
        ], 200);
    }

    public function getPqrsUserSesion()
    {
        $user = auth()->user();

        if ($user != null) {
            $pqrs = Pqrs::select('id_pqrs', 'codigo', 'fecha_registro', 'ven.name AS name_vendedor',
                'ven.apellidos AS apellidos_vendedor', 'cli.name AS name_cliente', 'cli.apellidos AS apellidos_cliente', 'estado')
                ->join('users AS ven', 'vendedor', '=', 'ven.id')
                ->join('users AS cli', 'cliente', '=', 'cli.id')
                ->where('vendedor', '=', $user->id)
                ->get();

            return response()->json([
                'response' => 'success',
                'status' => 200,
                'pqrs' => $pqrs,
            ], 200);
        }

        return response()->json([
            'response' => 'error',
            'status' => 403,
            'message' => 'La sesión del usuario a finalizado.',
        ], 403);
    }

    public function store(Request $request)
    {
        $request->validate([
            'vendedor' => ['required', 'exists:users,id'],
            'cliente' => ['required', 'exists:users,id'],
            'tipo' => ['required'],
            'mensaje' => ['required'],
        ]);

        try {
            DB::beginTransaction();

            $now = Carbon::now();

            $pqrs = new Pqrs();
            $pqrs->codigo = uniqid();
            $pqrs->fecha_registro = $now->format('Y-m-d');
            $pqrs->cliente = $request['cliente'];
            $pqrs->vendedor = $request['vendedor'];
            $pqrs->tipo = $request['tipo'];
            $pqrs->estado = 'abierto';
            $pqrs->save();

            DB::table('seguimiento_pqrs')->insert([
                'usuario' => $request['cliente'],
                'pqrs' => $pqrs->id_pqrs,
                'mensaje' => $request['mensaje'],
                'hora' => $now->format('H:i:s'),
            ]);

            DB::commit();

            return response()->json([
                'response' => 'success',
                'status' => 200,
            ], 200);

        } catch (\Exception $ex) {
            \Log::info($ex->getMessage());
            \Log::info($ex->getTraceAsString());
            DB::rollBack();

            return response()->json([
                'response' => 'error',
                'status' => 403,
                'message' => 'Error en la creación de la PQRS.',
            ], 403);
        }
    }

    public function show($id)
    {
        $user = auth()->user();

        $pqrs = Pqrs::select('id_pqrs', 'codigo', 'fecha_registro', 'pqrs.vendedor', 'pqrs.cliente', 'ven.name AS name_vendedor',
            'ven.apellidos AS apellidos_vendedor', 'cli.name AS name_cliente', 'cli.apellidos AS apellidos_cliente', 'estado')
            ->join('users AS ven', 'vendedor', '=', 'ven.id')
            ->join('users AS cli', 'cliente', '=', 'cli.id')
            ->where('id_pqrs', $id)
            ->when($user->rol_id == 3, function ($q) use ($user) {
                $q->where('cliente', $user->id);
            })
            ->when($user->rol_id == 2, function ($q) use ($user) {
                $q->where('vendedor', $user->id);
            })
            ->first();

        if ($pqrs == null) {
            return abort(404);
        }

        $messages_pqrs = DB::table('seguimiento_pqrs')
            ->select('seguimiento_pqrs.*', 'users.name', 'users.apellidos', 'users.rol_id')
            ->join('users', 'usuario', '=', 'id')
            ->where('pqrs', $id)
            ->orderBy('seguimiento_pqrs.created_at', 'ASC')
            ->get();

        foreach ($messages_pqrs as $messages) {
            $val_rol_user = $user->rol_id;
            $messages->iniciales = substr($messages->name, 0, 1);
            $messages->iniciales .= substr($messages->apellidos, 0, 1);
            $messages->hora = substr($messages->hora, 0, -3);

            if ($messages->rol_id == 2) {
                // Si el usuario autenticado es admin o vendedor. Se pone como destinatario o addressee al vendedor.
                if ($val_rol_user == 1 || $val_rol_user == 2) {
                    $messages->addressee = true;
                } else {
                    $messages->addressee = false;
                }
            } else if ($messages->rol_id == 3) {
                // Si el usuario autenticado es admin o vendedor. pero el mensaje lo diligencio un cliente
                if ($val_rol_user == 1 || $val_rol_user == 2) {
                    $messages->addressee = false;
                } else {
                    $messages->addressee = true;
                }
            } else {
                if ($val_rol_user == 1 || $val_rol_user == 2) {
                    $messages->addressee = true;
                } else {
                    $messages->addressee = false;
                }
            }
        }

        $pqrs->messages_pqrs = $messages_pqrs;

        // Busqueda de los pedidos en base al vendedor y cliente registrados en la pqrs.
        $pedidos = DB::table('pedidos')
            ->select('pedidos.*', 'users.name', 'users.apellidos', 'users.rol_id')
            ->join('users', 'cliente', '=', 'id')
            ->where('vendedor', $pqrs->vendedor)
            ->where('cliente', $pqrs->cliente)
            ->get();

        foreach ($pedidos as $pedido) {
            $pedido->iniciales = substr($pedido->name, 0, 1);
            $pedido->iniciales .= substr($pedido->apellidos, 0, 1);
        }

        $pqrs->pedidos = $pedidos;

        return response()->json([
            'response' => 'success',
            'status' => 200,
            'pqrs' => $pqrs,
        ], 200);
    }

    public function NewMessage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'mensaje' => ['required'],
            'usuario' => ['required', 'exists:users,id'],
            'pqrs' => ['required', 'exists:pqrs,id_pqrs'],
        ]);

        if ($validator->fails()) {
            return response()->json($validator->messages(), 403);
        }

        try {

            \DB::beginTransaction();

            $seguimiento = new SeguimientoPqrs();
            $seguimiento->usuario = $request['usuario'];
            $seguimiento->pqrs = $request['pqrs'];
            $seguimiento->mensaje = $request['mensaje'];
            $seguimiento->hora = Carbon::now()->format('H:i');
            $seguimiento->save();

            $message_pqrs = SeguimientoPqrs::select('seguimiento_pqrs.*', 'users.name', 'users.apellidos', 'users.rol_id')
                ->join('users', 'usuario', '=', 'id')
                ->where('id_seguimiento', $seguimiento->id_seguimiento)
                ->first();

            $val_rol_user = auth()->user()->rol_id;
            $message_pqrs->iniciales = substr($message_pqrs->name, 0, 1);
            $message_pqrs->iniciales .= substr($message_pqrs->apellidos, 0, 1);
            $message_pqrs->hora = substr($message_pqrs->hora, 0, -3);

            if ($message_pqrs->rol_id == 2) {
                // Si el usuario autenticado es admin o vendedor. Se pone como destinatario o addressee al vendedor.
                if ($val_rol_user == 1 || $val_rol_user == 2) {
                    $message_pqrs->addressee = true;
                } else {
                    $message_pqrs->addressee = false;
                }
            } else if ($message_pqrs->rol_id == 3) {
                // Si el usuario autenticado es admin o vendedor. pero el mensaje lo diligencio un cliente
                if ($val_rol_user == 1 || $val_rol_user == 2) {
                    $message_pqrs->addressee = false;
                } else {
                    $message_pqrs->addressee = true;
                }
            } else {
                if ($val_rol_user == 1 || $val_rol_user == 2) {
                    $message_pqrs->addressee = true;
                } else {
                    $message_pqrs->addressee = false;
                }
            }

            \DB::commit();

            // Enviar notificaciones push a usuarios.
            $this->sendNewMessageNotification($request);

            return response()->json([
                'response' => 'success',
                'status' => 200,
                'mensaje' => $message_pqrs,
            ], 200);

        } catch (\Exception $ex) {
            \Log::info($ex->getMessage());
            \Log::info($ex->getTraceAsString());
            \DB::rollBack();

            return response()->json([
                'response' => 'error',
                'status' => 403,
                'message' => 'Error creando el mensaje.',
            ], 403);
        }
    }

    private function sendNewMessageNotification(Request $request)
    {
        try {

            $pqrs_id = $request['pqrs'];
            $user_from = $request['usuario'];
            $message = substr($request['mensaje'], 0, 100) . '...';

            $pqrs = \DB::table('pqrs')
                ->where('id_pqrs', $pqrs_id)
                ->first();

            $usuarios_ids = \DB::table('seguimiento_pqrs')
                ->select('usuario')
                ->where('pqrs', $pqrs_id)
                ->where('usuario', '!=', $user_from)
                ->groupBy('usuario')
                ->get()
                ->pluck('usuario')
                ->all();

            if ($pqrs->cliente != $user_from) {
                $usuarios_ids[] = $pqrs->cliente;
            }
            if ($pqrs->vendedor != $user_from) {
                $usuarios_ids[] = $pqrs->vendedor;
            }

            $usuarios_ids = array_unique($usuarios_ids);

            $usuarios = User::query()
                ->whereIn('id', $usuarios_ids)
                ->whereNotNull('device_token')
                ->get();

            foreach ($usuarios as $usuario) {
                (new Notifications)->sendNotification($usuario, [
                    "title" => 'Nuevo mensaje',
                    "body" => $message,
                ], [
                    'type' => 'pqrs-message',
                    'id_pqrs' => $request['pqrs'],
                ]);
            }

        } catch (\Exception $ex) {
            \Log::info($ex->getMessage());
            \Log::info($ex->getTraceAsString());
        }
    }

    public function changeState($id, $state)
    {
        $pqrs = Pqrs::findOrFail($id);

        if ($state == 'abierto' || $state == 'cerrado') {

            $pqrs->estado = $state;

        } else {

            return response()->json([
                'response' => 'error',
                'status' => 403,
                'message' => 'El estado enviado no es valido.',
            ], 403);

        }

        $pqrs->save();

        return response()->json([
            'response' => 'success',
            'status' => 200,
            'message' => 'Estado asignado de manera correcta.',
        ], 200);
    }
}
