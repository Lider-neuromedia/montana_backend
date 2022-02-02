<?php

namespace App\Http\Controllers\Batch;

use App\Entities\Tienda;
use App\Entities\User;
use App\Entities\UserData;
use Carbon\Carbon;
use League\Csv\Reader;
use League\Csv\Statement;

class ImportarClientes extends Importar
{
    public function importar($contenido)
    {
        $csv = Reader::createFromString($contenido);
        $csv->setOutputBOM(Reader::BOM_UTF8);
        $csv->addStreamFilter('convert.iconv.ISO-8859-15/UTF-8');
        $csv->setDelimiter('|');

        $steps = 50;
        $count = $csv->count();
        $pages = ceil($csv->count() / $steps);

        $password = bcrypt(env("TEST_PASSWORD", \Str::random(12)));
        $diccionario = [];

        for ($page = 0; $page < $pages; $page++) {
            $stmt = Statement::create()
                ->offset($page * $steps)
                ->limit($steps);
            $records = $stmt->process($csv);

            foreach ($records as $record) {
                $idCliente = $record[0];
                $vendedorCodigo = $record[12];

                $nombresTemporal = $this->dividirNombre($record[2]);
                $nombres = $nombresTemporal->nombres;
                $nombreCliente = $nombres[0];
                $emailCliente = $record[9] ?: (\Str::slug($nombreCliente . $idCliente) . '@example.com');
                $nombreTienda = $nombres[0];
                $lugarTienda = $record[7];
                $localTienda = "";

                if ($nombresTemporal->conteo == 4) {
                    $nombreTienda = implode(" ", [$nombres[0], $nombres[1]]);
                    $lugarTienda = $nombres[2];
                    $localTienda = $nombres[3];
                } else if ($nombresTemporal->conteo == 3) {
                    $nombreTienda = implode(" ", [$nombres[0], $nombres[1]]);
                    $lugarTienda = $nombres[2];
                    $localTienda = "";
                } else if ($nombresTemporal->conteo == 2) {
                    $nombreTienda = $nombres[0];
                    $lugarTienda = $nombres[1];
                    $localTienda = "";
                }

                $datosCliente = [
                    'rol_id' => 3,
                    'name' => $nombreCliente,
                    'apellidos' => '', // TODO: El nombre completo viene en un solo campo
                    'email' => $emailCliente, // TODO: No viene en el archivo
                    'tipo_identificacion' => 'Cedula', // TODO: No viene en el archivo
                    'dni' => '', // TODO: No viene en el archivo
                    'password' => $password, // TODO: No viene en el archivo
                ];

                $datosTienda = [
                    "nombre" => $nombreTienda,
                    "lugar" => $lugarTienda, // Ciudad
                    "local" => $localTienda,
                    "direccion" => $record[4],
                    "telefono" => $record[8],
                    // TODO: Datos por validar si van en datos de la tienda
                    "fecha_ingreso" => $this->formatearFecha($record[10]),
                    "fecha_ultima_compra" => $this->formatearFecha($record[11]),
                    "cupo" => $record[15],
                    "ciudad_codigo" => $record[6],
                    "sucursal" => $record[1],
                    "zona" => $record[5],
                    "bloqueado" => $record[16],
                    "bloqueado_fecha" => $this->formatearFecha($record[17]),
                    "nombre_representante" => $record[3], // Vacío
                    "plazo" => $record[13],
                    "escala_factura" => $record[14],
                    "observaciones" => $record[18],
                ];

                $camposExtra = [
                    new UserData(["field_key" => "cliente_id", "value_key" => $idCliente]), // TODO: registros vacíos
                    new UserData(["field_key" => "nit", "value_key" => ""]), // TODO: registros vacíos
                    new UserData(["field_key" => "razon_social", "value_key" => ""]), // TODO: registros vacíos
                    new UserData(["field_key" => "direccion", "value_key" => ""]), // TODO: se tiene la dirección de la tienda
                    new UserData(["field_key" => "telefono", "value_key" => ""]), // TODO: se tiene el teléfono de la tienda
                ];

                if (!isset($diccionario["$idCliente"])) {
                    $diccionario["$idCliente"] = [
                        "conteo" => 0,
                        "clientes" => [],
                        "tiendas" => [],
                    ];
                }

                // $diccionario["$idCliente"]["record"] = $this->formatRecord($record);
                $diccionario["$idCliente"]["conteo"]++;
                $diccionario["$idCliente"]["vendedor_codigo"] = $vendedorCodigo;
                $diccionario["$idCliente"]["tiendas"][] = $datosTienda;
                $diccionario["$idCliente"]["clientes"][$datosCliente["name"]] = [
                    "cliente" => $datosCliente,
                    "extra" => $camposExtra,
                ];
            }
        }

        foreach ($diccionario as $clienteId => $grupoClientes) {
            $this->saveOrUpdateCliente(
                $clienteId,
                $grupoClientes["vendedor_codigo"],
                $grupoClientes["clientes"],
                $grupoClientes["tiendas"],
            );
        }

        return [
            'registros_ingresados' => $count,
            'registros_guardados' => count($diccionario),
            'clientes_total' => User::where('rol_id', 3)->count(),
            'tiendas_total' => Tienda::count(),
            // 'diccionario' => $diccionario,
        ];
    }

    private function saveOrUpdateCliente($idCliente, $vendedorCodigo, array $clientes, array $tiendas)
    {
        $conteoClientes = 0;

        // Guardar cada uno de los clientes de un grupo(cliente_id).
        foreach ($clientes as $nombreCliente => $data) {
            $conteoClientes++;
            $cliente = $this->buscarCliente($idCliente, $nombreCliente);

            // Guardar datos de cliente.
            if ($cliente == null) {
                $cliente = User::create($data['cliente']);
            } else {
                $cliente->update($data['cliente']);
            }

            // Guardar datos extra.
            $camposExtra = collect($data['extra'])
                ->map(function ($x) use ($cliente) {
                    $x->user_id = $cliente->id;
                    return $x;
                });
            $cliente->datos()->delete();
            $cliente->datos()->saveMany($camposExtra);

            // Asignar vendedor.
            $vendedor = $this->buscarVendedor($vendedorCodigo);
            \DB::table('vendedor_cliente')
                ->where('cliente', $cliente->id)
                ->delete();

            if ($vendedor) {
                \DB::table('vendedor_cliente')->insert([
                    'vendedor' => $vendedor->id,
                    'cliente' => $cliente->id,
                ]);
            }

            // Guardar tiendas. Solo asignarle tiendas al primer
            // cliente del grupo definido por el idCliente.
            if ($conteoClientes == 1) {
                foreach ($tiendas as $datosTienda) {
                    $datosTienda["cliente"] = $cliente->id;
                    $tienda = $this->buscarTienda($datosTienda);

                    if ($tienda == null) {
                        $tienda = Tienda::create($datosTienda);
                    } else {
                        $tienda->update($datosTienda);
                    }
                }
            }
        }
    }

    private function buscarCliente($idCliente, $nombre)
    {
        return User::query()
            ->where('rol_id', 3)
            ->where('name', $nombre)
            ->whereHas('datos', function ($q) use ($idCliente) {
                $q->where([
                    'field_key' => 'cliente_id',
                    'value_key' => $idCliente,
                ]);
            })
            ->first();
    }

    private function buscarTienda($datosTienda)
    {
        return Tienda::query()
            ->where("cliente", $datosTienda["cliente"])
            ->where("sucursal", $datosTienda["sucursal"])
            ->where("nombre", $datosTienda["nombre"])
            ->where("lugar", $datosTienda["lugar"])
            ->where("direccion", $datosTienda["direccion"])
            ->first();
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

    private function dividirNombre($nombre)
    {
        $nombres = collect(explode('/', $nombre));
        $nombres = $nombres->map(function ($x) {
            return trim($x);
        });
        $nombres = $nombres->filter(function ($value, $key) {
            return $value != "";
        });
        $conteo = $nombres->count();
        $nombres->toArray();

        if ($conteo == 0) {
            $nombres = [""];
        }

        return (Object) compact('nombres', 'conteo');
    }

    private function formatearFecha($fecha)
    {
        try {
            return Carbon::createFromFormat('Y-m-d', $fecha)->format('Y-m-d');
        } catch (\Exception $ex) {
            return null;
        }
    }

    private function formatRecord($record)
    {
        return (Object) [
            "CLIENTE" => $record[0], // cliente_id
            "SUCURSAL" => $record[1], // sucursal
            "NCLIENTE" => $record[2], // nombre
            "REPRESENTA" => $record[3], // nombre_representante
            "DIRECCION" => $record[4], // direccion
            "ZONA" => $record[5], // zona
            "CODICIUDAD" => $record[6], // ciudad_codigo
            "CIUDAD" => $record[7], // ciudad
            "TELEFONO" => $record[8], // telefono
            "EMAIL" => $record[9], // email
            "INGRESO" => $record[10], // fecha_ingreso
            "ULTIMA" => $record[11], // fecha_ultima_compra
            "CVENDEDOR" => $record[12], // vendedor_codigo
            "PLAZO" => $record[13], // plazo
            "ESCALA" => $record[14], // escala_factura
            "CUPO" => $record[15], // cupo
            "BLOQUEO" => $record[16], // bloqueado
            "FBLOQUEO" => $record[17], // bloqueado_fecha
            "OBSERVA" => $record[18], // observaciones
        ];
    }
}
