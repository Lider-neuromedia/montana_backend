<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCarterasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('carteras', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('identificador');
            $table->string('sucursal')->nullable()->default('');
            $table->string('tipo');
            $table->string('factura');
            $table->date('fecha_factura');
            $table->date('fecha_vencimiento');
            $table->string('total');
            $table->string('saldo');
            $table->bigInteger('vendedor_id')->nullable()->unsigned();
            $table->foreign('vendedor_id')->references('id')->on('users')->onDelete('set null');
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
        Schema::dropIfExists('carteras');
    }
}
