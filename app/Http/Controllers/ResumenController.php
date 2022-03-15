<?php

namespace App\Http\Controllers;

use App\Entities\AmpliacionCupo;
use App\Entities\Cartera;
use App\Entities\Pedido;
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

        $fecha = $request->get('fecha_atendidos') ? Carbon::createFromFormat('Y-m', $request->get('fecha_atendidos')) : false;
        $usuario = auth()->user();

        // Vendedor
        if ($usuario->rol_id == 2) {
            $cantidad_clientes = $usuario->clientes()->count(); // Cantidad de clientes
            $cantidad_pedidos = $this->calcularCantidadPedidos($usuario);

            // Cantidad de clientes atendidos
            $cantidad_clientes_atendidos = Pedido::query()
                ->where('vendedor', $usuario->id)
                ->when($fecha, function ($q) use ($fecha) {
                    $q->whereMonth('fecha', $fecha)
                        ->whereYear('fecha', $fecha);
                })
                ->groupBy('cliente')
                ->count();

            return response()->json(compact(
                'cantidad_clientes',
                'cantidad_clientes_atendidos',
                'cantidad_pedidos',
            ), 200);
        }

        // Cliente
        if ($usuario->rol_id == 3) {
            $cantidad_tiendas = $usuario->tiendas()->count(); // Tiendas Creadas
            $cantidad_pqrs = $usuario->clienteTickets()->count(); // PQRS generados
            $cantidad_pedidos = $this->calcularCantidadPedidos($usuario);

            return response()->json(compact(
                'cantidad_tiendas',
                'cantidad_pqrs',
                'cantidad_pedidos',
            ), 200);
        }

        return response()->json(['message' => 'Información no disponible.'], 500);
    }

    private function calcularCantidadPedidos(User $usuario)
    {
        $pedidos = Pedido::query()
            ->select('estado', \DB::raw('count(*) as pedidos'))
            ->when($usuario->rol_id == 2, function ($q) use ($usuario) {
                $q->where('vendedor', $usuario->id);
            })
            ->when($usuario->rol_id == 3, function ($q) use ($usuario) {
                $q->where('cliente', $usuario->id);
            })
            ->groupBy('estado')
            ->get();

        return [
            'realizados' => $pedidos->sum('pedidos'),
            'aprobados' => $pedidos->where('estado', 1)->first()->pedidos ?? 0,
            'pendientes' => $pedidos->where('estado', 2)->first()->pedidos ?? 0,
            'rechazados' => $pedidos->where('estado', 3)->first()->pedidos ?? 0,
        ];
    }

    public function resumenCartera(User $cliente)
    {
        return response()->json(self::resumenCarteraCliente($cliente), 200);
    }

    public static function resumenCarteraCliente(User $cliente)
    {
        $dni = $cliente->obtenerDato('nit') ?? $cliente->dni;
        $cupoTotal = $cliente->tiendas()->sum('cupo');
        $pedidosTotal = $cliente->clientePedidos()->where('estado', 2)->sum('total');

        $cupoPreaprobado = AmpliacionCupo::query()
            ->where('cliente', $cliente->id)
            ->where('estado', 'pendiente')
            ->get()
            ->sum('monto');

        $saldoTotal = Cartera::query()
            ->where('identificador', $dni)
            ->get()
            ->sum('saldo');

        $saldoTotalMora = Cartera::query()
            ->where('identificador', $dni)
            ->whereDate('fecha_vencimiento', '<', Carbon::now()->format('Y-m-d'))
            ->get()
            ->sum('saldo');

        $cupoDisponible = $cupoTotal - $pedidosTotal - $saldoTotalMora;

        return [
            'cliente_id' => $cliente->id,
            'cupo_preaprobado' => $cupoPreaprobado,
            'cupo_disponible' => $cupoDisponible,
            'saldo_total_deuda' => $saldoTotal,
            'saldo_mora' => $saldoTotalMora,
        ];
    }
}
