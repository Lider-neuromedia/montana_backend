<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateValoracionesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('valoraciones', function (Blueprint $table) {
            $table->increments('id_valoracion');
            $table->unsignedInteger('pregunta')->index('fk_pregunta_valoracion');
            $table->unsignedBigInteger('usuario')->default(0)->index('fk_usuario_valoracion');
            $table->unsignedInteger('producto')->index('fk_producto_valoracion');
            $table->integer('respuesta');
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('valoraciones');
    }
}
