<?php

namespace App\Utils;

use DB;
use Illuminate\Support\Facades\Schema;
use Storage;

class TestData
{
    const TABLES = [
        'ciudades',
        'oauth_access_tokens',
        'pedido_productos',
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

        if (\DB::table('migrations')->count() == 0) {
            self::migrateTable('migrations', "database/migrations.json");
        }

        foreach (self::TABLES as $table) {
            $path = "database/$table.json";
            self::migrateTable($table, $path);
        }

        Schema::enableForeignKeyConstraints();
    }

    private static function migrateTable($table, $path)
    {
        \Log::info("seed: $path");

        if (Storage::exists($path) && Schema::hasTable($table)) {
            $rows = (Array) json_decode(Storage::get($path));

            foreach ($rows as $data) {
                DB::table($table)->insert((Array) $data);
            }
        }
    }
}
