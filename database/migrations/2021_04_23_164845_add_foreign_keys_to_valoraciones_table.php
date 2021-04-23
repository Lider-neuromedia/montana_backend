<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToValoracionesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('valoraciones', function (Blueprint $table) {
            $table->foreign('pregunta', 'fk_pregunta_valoracion')->references('id_pregunta')->on('preguntas')->onUpdate('NO ACTION')->onDelete('NO ACTION');
            $table->foreign('producto', 'fk_producto_valoracion')->references('id_producto')->on('productos')->onUpdate('NO ACTION')->onDelete('NO ACTION');
            $table->foreign('usuario', 'fk_usuario_valoracion')->references('id')->on('users')->onUpdate('NO ACTION')->onDelete('NO ACTION');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('valoraciones', function (Blueprint $table) {
            $table->dropForeign('fk_pregunta_valoracion');
            $table->dropForeign('fk_producto_valoracion');
            $table->dropForeign('fk_usuario_valoracion');
        });
    }
}
