<?php

namespace App\Utils;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use DB;
use Storage;

class TestData
{
    const TABLES = [
        'ciudades',
        'oauth_access_tokens',
        'pedido_productos',
        'migrations',
        'valoraciones',
        'departamentos',
        'user_data',
        'galeria_productos',
        'seguimiento_pqrs',
        'novedades',
        'pedidos',
        'users',
        'ampliacion_cupo',
        'catalogos',
        'preguntas',
        'tiendas',
        'productos',
        'pqrs',
        'vendedor_cliente',
        'encuestas',
        'oauth_clients',
        'estados',
        'roles',
        'oauth_personal_access_clients',
        'categorias',
        'categorias_productos',
        'marcas',
    ];

    /**
     * Obtener datos de las tablas de bases de datos y guardarlos en archivos json.
     * Usar con la base de datos original.
     */
    public static function saveFromDataBase()
    {
        foreach (self::TABLES as $table) {
            if (Schema::hasTable($table)) {
                $data = DB::table($table)->get()->toArray();
                Storage::put("database/$table.json", json_encode($data));
            }
        }
    }

    /**
     * Guardar datos de los archivos json en las tablas de bases de datos.
     * Usar con la base de datos nueva.
     */
    public static function saveToDataBase()
    {
        Schema::disableForeignKeyConstraints();

        foreach (self::TABLES as $table) {
            $path = "database/$table.json";

            if (Storage::exists($path) && Schema::hasTable($table)) {
                $rows = (Array)json_decode(Storage::get($path));

                foreach ($rows as $data) {
                    DB::table($table)->insert((Array)$data);
                }
            }
        }

        Schema::enableForeignKeyConstraints();
    }
}
