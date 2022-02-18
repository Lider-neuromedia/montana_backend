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
                $nit = $record[0];

                $nombresTemporal = $this->dividirNombre($record[2]);
                $nombres = $nombresTemporal->nombres;
                $nombreCliente = $nombres[0];
                $razonSocial = $nombres[0];
                $emailCliente = $record[9] ?: \Str::slug("{$nombreCliente}{$nit}") . "@example.com";
                $nombreTienda = $nombres[0];
                $lugarTienda = $record[7];
                $localTienda = "";

                if ($nombresTemporal->conteo == 4) {
                    $nombreTienda = $nombres[1];
                    $razonSocial = $nombres[1];
                    $lugarTienda = $nombres[2];
                    $localTienda = $nombres[3];
                } else if ($nombresTemporal->conteo == 3) {
                    $nombreTienda = $nombres[1];
                    $razonSocial = $nombres[1];
                    $lugarTienda = $nombres[2];
                    $localTienda = "";
                } else if ($nombresTemporal->conteo == 2) {
                    $nombreTienda = $nombres[0];
                    $razonSocial = $nombres[0];
                    $lugarTienda = $nombres[1];
                    $localTienda = "";
                }

                $datosCliente = [
                    'rol_id' => 3,
                    'name' => $nombreCliente,
                    'apellidos' => '',
                    'email' => $emailCliente,
                    'tipo_identificacion' => 'Cedula',
                    'dni' => $nit,
                    'password' => $password,
                ];

                $datosTienda = [
                    "nombre" => $nombreTienda,
                    "lugar" => $lugarTienda,
                    "local" => $localTienda,
                    "direccion" => $record[4],
                    "telefono" => $record[8],
                    "fecha_ingreso" => $this->formatearFecha($record[10]),
                    "fecha_ultima_compra" => $this->formatearFecha($record[11]),
                    "cupo" => $record[15],
                    "ciudad_codigo" => $record[6],
                    "sucursal" => $record[1],
                    "zona" => $record[5],
                    "bloqueado" => $record[16],
                    "bloqueado_fecha" => $this->formatearFecha($record[17]),
                    "nombre_representante" => $record[3],
                    "plazo" => $record[13],
                    "escala_factura" => $record[14],
                    "observaciones" => $record[18],
                    "vendedor_codigo" => $record[12],
                ];

                $camposExtra = [
                    new UserData(["field_key" => "nit", "value_key" => $nit]),
                    new UserData(["field_key" => "razon_social", "value_key" => $razonSocial]),
                ];

                if (!isset($diccionario["$nit"])) {
                    $diccionario["$nit"] = [
                        "conteo" => 0,
                        "clientes" => [],
                        "tiendas" => [],
                        "telefonos" => [],
                        "direcciones" => [],
                    ];
                }

                $diccionario["$nit"]["conteo"]++;
                $diccionario["$nit"]["telefonos"][] = $record[8];
                $diccionario["$nit"]["direcciones"][] = $record[4];
                $diccionario["$nit"]["tiendas"][] = $datosTienda;
                $diccionario["$nit"]["clientes"][$datosCliente["name"]] = [
                    "cliente" => $datosCliente,
                    "extra" => $camposExtra,
                ];
            }
        }

        try {
            \DB::beginTransaction();

            foreach ($diccionario as $nit => $grupoClientes) {
                $this->saveOrUpdateCliente($nit, $grupoClientes);
            }

            \DB::commit();
        } catch (\Exception $ex) {
            \Log::info($ex->getMessage());
            \Log::info($ex->getTraceAsString());
            \DB::rollBack();
        }

        return [
            'registros_ingresados' => $count,
            'registros_guardados' => count($diccionario),
            'clientes_total' => User::where('rol_id', 3)->count(),
            'tiendas_total' => Tienda::count(),
        ];
    }

    private function saveOrUpdateCliente($nit, $grupoClientes)
    {
        $conteoClientes = 0;
        $clientes = $grupoClientes["clientes"];
        $tiendas = $grupoClientes["tiendas"];

        $telefonoCliente = new UserData([
            "field_key" => "telefono",
            "value_key" => implode(", ", array_unique($grupoClientes["telefonos"])),
        ]);
        $direccionCliente = new UserData([
            "field_key" => "direccion",
            "value_key" => implode(", ", array_unique($grupoClientes["direcciones"])),
        ]);

        // Guardar cada uno de los clientes de un grupo(nit).
        foreach ($clientes as $nombreCliente => $data) {
            $conteoClientes++;
            $cliente = $this->buscarCliente($nit, $nombreCliente);

            // Limpiar relaciones entre clientes - tiendas y vendedores - tiendas.
            if ($conteoClientes == 1 && $cliente != null) {
                $cliente->vendedores()->detach();
                foreach ($cliente->tiendas()->get() as $tienda) {
                    $tienda->vendedores()->detach();
                }
            }

            // Guardar datos de cliente.
            if ($cliente == null) {
                $cliente = User::create($data['cliente']);
            } else {
                $cliente->update($data['cliente']);
            }

            // Guardar datos extra.
            $data['extra'][] = $telefonoCliente;
            $data['extra'][] = $direccionCliente;
            $camposExtra = collect($data['extra'])
                ->map(function ($x) use ($cliente) {
                    $x->user_id = $cliente->id;
                    return $x;
                });
            $cliente->datos()->delete();
            $cliente->datos()->saveMany($camposExtra);

            // Guardar tiendas. Solo asignarle tiendas al primer
            // cliente del grupo definido por el nit.
            if ($conteoClientes == 1) {
                foreach ($tiendas as $datosTienda) {
                    $datosTienda["cliente"] = $cliente->id;
                    $tienda = $this->buscarTienda($datosTienda);

                    if ($tienda == null) {
                        $tienda = Tienda::create($datosTienda);
                    } else {
                        $tienda->update($datosTienda);
                    }

                    // Asignar vendedor.
                    $vendedor = $this->buscarVendedor($datosTienda["vendedor_codigo"]);
                    if ($vendedor) {
                        $tienda->vendedores()->syncWithoutDetaching([$vendedor->id]);
                        $cliente->vendedores()->syncWithoutDetaching([$vendedor->id]);
                    }
                }
            }
        }
    }

    private function buscarCliente($nit, $nombre)
    {
        return User::query()
            ->where('rol_id', 3)
            ->where('name', $nombre)
            ->whereHas('datos', function ($q) use ($nit) {
                $q->where([
                    'field_key' => 'nit',
                    'value_key' => $nit,
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
            "CLIENTE" => $record[0], // nit
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
