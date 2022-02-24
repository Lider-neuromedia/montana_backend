<?php

namespace App\Http\Controllers;

use App\Entities\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ResumenController extends Controller
{
    public function dashboardResumen(Request $request)
    {
        $request->validate([
            'fecha_atendidos' => ["nullable", "date_format:Y-m"],
        ]);

        $fecha = $request->get('fecha_atendidos') ?: false;
        $fecha = $fecha ? Carbon::createFromFormat('Y-m', $fecha) : false;

        $user = auth()->user();

        if ($user->rol_id == 2) { // Vendedor

            // Cantidad de clientes
            $cantidad_clientes = $user->clientes()->count();

            // Cantidad de clientes atendidos
            $cantidad_clientes_atendidos = $user->vendedorTickets()
                ->where('estado', 'cerrado')
                ->when($fecha, function ($q) use ($fecha) {
                    $q->whereMonth('created_at', $fecha)
                        ->whereYear('created_at', $fecha);
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
            $cantidad_tiendas = $user->tiendas()->count();

            // PQRS generados
            $cantidad_pqrs = $user->clienteTickets()->count();

            $cantidad_pedidos = $this->calcularCantidadPedidos($user);

            return response()->json(compact(
                'cantidad_tiendas',
                'cantidad_pqrs',
                'cantidad_pedidos'
            ), 200);
        }

        return response()->json([], 200);
    }

    private function calcularCantidadPedidos(User $user)
    {
        $cantidad_pedidos = [];
        $realizados = 0;
        $aprobados = 0;
        $pendientes = 0;
        $rechazados = 0;

        if ($user->rol_id == 2) {
            $cantidad_pedidos = $user->vendedorPedidos()
                ->select('estado', \DB::raw('count(*) as cantidad'))
                ->groupBy('estado')
                ->get();
        } else if ($user->rol_id == 3) {
            $cantidad_pedidos = $user->clientePedidos()
                ->select('estado', \DB::raw('count(*) as cantidad'))
                ->groupBy('estado')
                ->get();
        }

        foreach ($cantidad_pedidos as $cp) {
            $realizados += $cp->cantidad;

            if ($cp->estado == 1) {
                $aprobados = $cp->cantidad;
            } else if ($cp->estado == 2) {
                $pendientes = $cp->cantidad;
            } else if ($cp->estado == 3) {
                $rechazados = $cp->cantidad;
            }
        }

        return [
            'realizados' => $realizados,
            'aprobados' => $aprobados,
            'pendientes' => $pendientes,
            'rechazados' => $rechazados,
        ];
    }
}
