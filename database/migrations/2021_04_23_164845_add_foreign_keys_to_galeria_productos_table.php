<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToGaleriaProductosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('galeria_productos', function (Blueprint $table) {
            $table->foreign('producto', 'fk_producto')->references('id_producto')->on('productos')->onUpdate('NO ACTION')->onDelete('NO ACTION');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('galeria_productos', function (Blueprint $table) {
            $table->dropForeign('fk_producto');
        });
    }
}
