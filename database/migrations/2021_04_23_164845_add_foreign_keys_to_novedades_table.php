<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToNovedadesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('novedades', function (Blueprint $table) {
            $table->foreign('pedido', 'fk_pedido_novedad')->references('id_pedido')->on('pedidos')->onUpdate('NO ACTION')->onDelete('NO ACTION');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('novedades', function (Blueprint $table) {
            $table->dropForeign('fk_pedido_novedad');
        });
    }
}
