<?php

namespace App\Http\Controllers;

use OwenIt\Auditing\Models\Audit;

class MonitoreoController extends Controller
{
    public function index()
    {
        if (auth()->user()->rol_id !== 1) {
            return response()->json([
                'message' => 'Usted no tiene permisos para acceder a esta información.',
                'response' => 'error',
                'status' => 401,
            ], 200);
        }

        $audits = Audit::query()
            ->orderBy('created_at', 'desc')
            ->with(['user' => function ($q) {
                $q->select('id', 'rol_id', 'name', 'apellidos', 'dni', 'tipo_identificacion');
            }])
            ->paginate(10);

        return response()->json([
            'audits' => $audits,
            'response' => 'success',
            'status' => 200,
        ], 200);
    }
}
