<?php

namespace App\Exports;

use App\Entities\Pedido;
use App\Entities\User;
use App\Entities\PedidoProduct;
use App\Entities\Novedades;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;

class PedidoExport implements FromQuery, WithHeadings
{

    use Exportable;
    
    // public function __construct(int $id){
    //     $this->id = $id;
    // }

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
            'Notas',
            'Estado',
        ];
    }

    public function query(){
        return Pedido::query()->select('cli.name AS cliente,','fecha', 'codigo', 'metodo_pago', 'total',
                'vend.name AS vendedor','descuento', 'notas','estados.estado')
            ->join('estados', 'pedidos.estado', '=', 'id_estado')
            ->join('users as vend', 'pedidos.vendedor', '=', 'vend.id')
            ->join('users as cli', 'pedidos.cliente', '=', 'cli.id');
            // ->where('id_pedido', $this->id);
    }
}