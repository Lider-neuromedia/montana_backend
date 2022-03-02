<?php

namespace App\Exports;

use App\Entities\Pedido;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;

class PedidoExport implements FromQuery, WithHeadings
{
    use Exportable;

    public function headings(): array
    {
        return [
            'Cliente',
            'Fecha',
            'Codigo',
            'Metodo de pago',
            'Total',
            'Vendedor',
            'Descuento',
            'Notas Descuentos',
            'Notas Facturación',
            'Estado',
        ];
    }

    public function query()
    {
        return Pedido::query()
            ->select(
                'c.name AS cliente,',
                'fecha',
                'codigo',
                'metodo_pago',
                'total',
                'v.name AS vendedor',
                'descuento',
                'notas',
                'notas_facturacion',
                'e.estado')
            ->join('estados as e', 'pedidos.estado', '=', 'e.id_estado')
            ->join('users as v', 'pedidos.vendedor', '=', 'v.id')
            ->join('users as c', 'pedidos.cliente', '=', 'c.id');
    }
}
