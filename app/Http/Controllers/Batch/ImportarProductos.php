<?php

namespace App\Http\Controllers\Batch;

use App\Entities\Catalogo;
use App\Entities\Marca;
use App\Entities\Producto;
use League\Csv\Reader;
use League\Csv\Statement;

class ImportarProductos extends Importar
{
    public function importar($contenido)
    {
        $csv = Reader::createFromString($contenido);
        $csv->setDelimiter('|');

        $steps = 5;
        $count = $csv->count();
        $pages = ceil($csv->count() / $steps);
        $count_productos = 0;

        $defaultMarca = Marca::firstOrFail();
        $marcas = Marca::all();
        $defaultCatalogo = Catalogo::firstOrFail();

        for ($page = 0; $page < $pages; $page++) {
            $stmt = Statement::create()
                ->offset($page * $steps)
                ->limit($steps);

            $records = $stmt->process($csv);

            foreach ($records as $record) {
                $marca = $marcas->where('codigo', $record[0])->first();
                $marca = $marca ?: $defaultMarca;
                $catalogo = $defaultCatalogo;

                $data = [
                    // TODO: Revisar. valores no usados en la plataforma.
                    // 'ubicacion' => $record[4],
                    // 'peso' => $record[5],
                    // 'saldo' => $record[9],
                    'nombre' => $record[2],
                    'codigo' => $record[1],
                    'referencia' => $record[3],
                    'precio' => $record[7], // TODO: Revisar. precio_a
                    'total' => $record[8], // TODO: Revisar. precio_e
                    'stock' => 0, // revisar vacio
                    'descripcion' => '', // revisar vacio
                    'sku' => '', // revisar vacio
                    'descuento' => 0, // revisar vacio
                    'iva' => $record[6],
                    'catalogo' => $catalogo->id_catalogo, // revisar vacio
                    'marca' => $marca->id_marca,
                ];

                $count_productos++;
                $this->saveOrUpdateProducto($data);
            }
        }

        Catalogo::corregirCantidadDeProductosEnCatalogos();

        return [
            'registros_ingresados' => $count,
            'productos_guardados' => $count_productos,
            'productos_total' => Producto::count(),
        ];
    }

    private function saveOrUpdateProducto($data)
    {
        $producto = Producto::query()
            ->where('codigo', $data['codigo'])
            ->first();

        if ($producto == null) {
            Producto::create($data);
        } else {
            $producto->update($data);
        }
    }
}
