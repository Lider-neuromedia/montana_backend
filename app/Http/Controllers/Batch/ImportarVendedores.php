<?php

namespace App\Http\Controllers\Batch;

use App\Entities\User;
use App\Entities\UserData;
use League\Csv\Reader;
use League\Csv\Statement;

class ImportarVendedores extends Importar
{
    public function importar($contenido)
    {
        $csv = Reader::createFromString($contenido);
        $csv->setOutputBOM(Reader::BOM_UTF8);
        $csv->addStreamFilter('convert.iconv.ISO-8859-15/UTF-8');
        $csv->setDelimiter('|');

        $steps = 5;
        $count = $csv->count();
        $pages = ceil($csv->count() / $steps);
        $count_vendedores = 0;

        for ($page = 0; $page < $pages; $page++) {
            $stmt = Statement::create()
                ->offset($page * $steps)
                ->limit($steps);

            $records = $stmt->process($csv);

            foreach ($records as $record) {
                $data = [
                    'codigo' => $record[0],
                    'nombre' => $record[1],
                ];
                $count_vendedores++;
                $this->saveOrUpdateVendedor($data);
            }
        }

        return [
            'registros_ingresados' => $count,
            'registros_guardados' => $count_vendedores,
            'vendedores_total' => User::where('rol_id', 2)->count(),
        ];
    }

    private function saveOrUpdateVendedor($data)
    {
        $vendedor = User::query()
            ->where('rol_id', 2)
            ->whereHas('datos', function ($q) use ($data) {
                $q->where([
                    'field_key' => 'codigo',
                    'value_key' => $data['codigo'],
                ]);
            })->first();

        $dataVendedor = [
            'rol_id' => 2,
            'name' => $data['nombre'],
            'apellidos' => '', // TODO: revisar
            'dni' => '', // TODO: revisar
            'tipo_identificacion' => 'Cedula', // TODO: revisar
            'email' => \Str::slug($data['nombre']) . \Str::slug($data['codigo']) . '@example.com', // TODO: revisar
            'password' => bcrypt($data['codigo']), // TODO: revisar
        ];

        if ($vendedor == null) {
            $vendedor = User::create($dataVendedor);
        } else {
            $vendedor->update($dataVendedor);
        }

        $datos = [
            ['field_key' => 'codigo', 'value_key' => $data['codigo']],
            ['field_key' => 'telefono', 'value_key' => ''], // TODO: revisar
        ];

        foreach ($datos as $datosCampo) {
            $campo = $vendedor->datos()
                ->where('field_key', $datosCampo["field_key"])
                ->first();

            if ($campo == null) {
                $vendedor->datos()->save(new UserData($datosCampo));
            } else {
                $campo->update($datosCampo);
            }
        }
    }
}
