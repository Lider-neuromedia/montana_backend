<?php

namespace App\Http\Controllers\Batch;

use App\Entities\Cartera;
use App\Entities\User;
use League\Csv\Reader;
use League\Csv\Statement;

class ImportarCartera extends Importar
{
    public function importar($contenido)
    {
        $csv = Reader::createFromString($contenido);
        $csv->setDelimiter('|');

        $steps = 5;
        $count = $csv->count();
        $pages = ceil($csv->count() / $steps);
        $countRegistros = 0;
        $diccionario = [];

        for ($page = 0; $page < $pages; $page++) {
            $stmt = Statement::create()
                ->offset($page * $steps)
                ->limit($steps);
            $records = $stmt->process($csv);

            foreach ($records as $record) {
                $vendedorCodigo = $record[4];
                $idCliente = $record[0];

                $carteraData = [
                    'identificador' => $idCliente,
                    'sucursal' => $record[1],
                    'tipo' => $record[2],
                    'factura' => $record[3],
                    'fecha_factura' => $record[5],
                    'fecha_vencimiento' => $record[6],
                    'total' => $record[7],
                    'saldo' => $record[8],
                ];

                if (!isset($diccionario[$idCliente])) {
                    $diccionario[$idCliente]['conteo'] = 0;
                    $diccionario[$idCliente]['carteras'] = [];
                    $diccionario[$idCliente]['vendedor_existe'] = $this->buscarVendedor($vendedorCodigo) != null;
                }
                $diccionario[$idCliente]['conteo']++;
                $diccionario[$idCliente]['carteras'][] = $carteraData;

                $this->saveOrUpdateCartera($vendedorCodigo, $carteraData);
                $countRegistros++;
            }
        }

        return [
            'registros_ingresados' => $count,
            'carteras_guardadas' => $countRegistros,
            'carteras_total' => Cartera::count(),
            // $diccionario,
        ];
    }

    private function saveOrUpdateCartera($vendedorCodigo, $carteraData)
    {
        $cartera = Cartera::query()
            ->where([
                'identificador' => $carteraData['identificador'],
                'sucursal' => $carteraData['sucursal'],
                'tipo' => $carteraData['tipo'],
                'factura' => $carteraData['factura'],
            ])
            ->first();

        if ($cartera == null) {
            $cartera = Cartera::create($carteraData);
        } else {
            $cartera->update($carteraData);
        }

        $vendedor = $this->buscarVendedor($vendedorCodigo);

        if ($vendedor != null) {
            $cartera->vendedor()->associate($vendedor);
            $cartera->save();
        }
    }

    private function buscarVendedor($codigo)
    {
        return User::query()
            ->where('rol_id', 2)
            ->whereHas('datos', function ($q) use ($codigo) {
                $q->where([
                    'field_key' => 'codigo',
                    'value_key' => $codigo,
                ]);
            })
            ->first();
    }

    private function buscarClientes($idCliente)
    {
        return User::query()
            ->where('rol_id', 3)
            ->whereHas('datos', function ($q) use ($idCliente) {
                $q->where([
                    'field_key' => 'cliente_id',
                    'value_key' => $idCliente,
                ]);
            })
            ->get();
    }
}
