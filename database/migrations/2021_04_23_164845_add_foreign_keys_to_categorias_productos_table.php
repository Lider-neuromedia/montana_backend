<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToCategoriasProductosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('categorias_productos', function (Blueprint $table) {
            $table->foreign('categoria', 'fk_categoria_prod')->references('id_categoria')->on('categorias')->onUpdate('NO ACTION')->onDelete('NO ACTION');
            $table->foreign('producto', 'fk_producto_cat')->references('id_producto')->on('productos')->onUpdate('NO ACTION')->onDelete('NO ACTION');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('categorias_productos', function (Blueprint $table) {
            $table->dropForeign('fk_categoria_prod');
            $table->dropForeign('fk_producto_cat');
        });
    }
}
