<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCatalogosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('catalogos', function (Blueprint $table) {
            $table->increments('id_catalogo');
            $table->string('estado', 25)->nullable();
            $table->string('tipo', 25)->nullable();
            $table->text('imagen')->nullable();
            $table->string('titulo', 45)->nullable();
            $table->integer('cantidad')->nullable();
            $table->unsignedInteger('descuento')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('catalogos');
    }
}
