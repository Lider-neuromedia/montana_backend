<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAmpliacionCupoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ampliacion_cupo', function (Blueprint $table) {
            $table->integer('id_cupo', true);
            $table->string('codigo_solicitud', 50)->nullable();
            $table->date('fecha_solicitud')->nullable();
            $table->unsignedBigInteger('vendedor')->index('fk_ampliacion_vendedor');
            $table->unsignedBigInteger('cliente')->index('fk_ampliacion_cliente');
            $table->string('doc_identidad', 200);
            $table->string('doc_rut', 200);
            $table->string('doc_camara_com', 200);
            $table->integer('monto');
            $table->string('estado', 50);
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
        Schema::dropIfExists('ampliacion_cupo');
    }
}
