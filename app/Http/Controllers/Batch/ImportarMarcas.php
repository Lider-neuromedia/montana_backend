<?php

namespace App\Http\Controllers\Batch;

use App\Entities\Marca;
use League\Csv\Reader;
use League\Csv\Statement;

class ImportarMarcas extends Importar
{
    public function importar($contenido)
    {
        $csv = Reader::createFromString($contenido);
        $csv->setDelimiter('|');

        $steps = 5;
        $count = $csv->count();
        $pages = ceil($csv->count() / $steps);
        $count_marcas = 0;

        for ($page = 0; $page < $pages; $page++) {
            $stmt = Statement::create()
                ->offset($page * $steps)
                ->limit($steps);
            $records = $stmt->process($csv);

            foreach ($records as $record) {
                $data = [
                    'codigo' => $record[0],
                    'nombre_marca' => $record[1],
                ];
                $count_marcas++;
                $this->saveOrUpdateMarca($data);
            }
        }

        return [
            'registros_ingresados' => $count,
            'marcas_guardadas' => $count_marcas,
            'marcas_total' => Marca::count(),
        ];
    }

    private function saveOrUpdateMarca($data)
    {
        $existeConNombreYCodigo = Marca::query()
            ->where('nombre_marca', $data['nombre_marca'])
            ->where('codigo', $data['codigo'])
            ->exists();

        if (!$existeConNombreYCodigo) {
            Marca::create($data);
        } else {
            $encuentraPorNombre = Marca::query()
                ->where('nombre_marca', $data['nombre_marca'])
                ->first();
            $encuentraPorCodigo = Marca::query()
                ->where('codigo', $data['codigo'])
                ->first();

            if ($encuentraPorNombre) {
                $encuentraPorNombre->update($data);
            } else if ($encuentraPorCodigo) {
                $encuentraPorCodigo->update($data);
            }
        }
    }
}
