<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTiendaVendedorTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tienda_vendedor', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('tienda_id');
            $table->unsignedBigInteger('vendedor_id');
            $table->foreign('tienda_id')->references('id_tiendas')->on('tiendas')->onDelete('cascade');
            $table->foreign('vendedor_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tienda_vendedor');
    }
}
