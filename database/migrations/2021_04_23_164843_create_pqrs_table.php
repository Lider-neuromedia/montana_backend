<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePqrsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pqrs', function (Blueprint $table) {
            $table->bigIncrements('id_pqrs');
            $table->string('codigo', 50)->nullable();
            $table->date('fecha_registro')->nullable();
            $table->unsignedBigInteger('cliente')->index('fk_pqrs_client');
            $table->unsignedBigInteger('vendedor')->index('fk_pqrs_vendedor');
            $table->string('tipo', 150)->default('');
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
        Schema::dropIfExists('pqrs');
    }
}
