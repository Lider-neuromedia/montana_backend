<?php

namespace App\Utils;

class Utils
{
    public static function corregirCantidadDeProductosEnCatalogos()
    {
        // Reiniciar contador de todos los catalogos.
        \DB::table('catalogos')
            ->whereNull('deleted_at')
            ->update(['cantidad' => 0]);

        $cantidades = \DB::table('productos')
            ->select('catalogo', \DB::raw('count(*) as cantidad'))
            ->whereNull('deleted_at')
            ->groupBy('catalogo')
            ->get();

        // Actualizar cantidades de productos.
        foreach ($cantidades as $c) {
            \DB::table('catalogos')
                ->where('id_catalogo', $c->catalogo)
                ->update(['cantidad' => $c->cantidad]);
        }

        return $cantidades;
    }
}
