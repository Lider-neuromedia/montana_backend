<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToProductosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('productos', function (Blueprint $table) {
            $table->foreign('catalogo', 'fk_catalogo')->references('id_catalogo')->on('catalogos')->onUpdate('NO ACTION')->onDelete('NO ACTION');
            $table->foreign('marca', 'fk_marca')->references('id_marca')->on('marcas')->onUpdate('NO ACTION')->onDelete('NO ACTION');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('productos', function (Blueprint $table) {
            $table->dropForeign('fk_catalogo');
            $table->dropForeign('fk_marca');
        });
    }
}
