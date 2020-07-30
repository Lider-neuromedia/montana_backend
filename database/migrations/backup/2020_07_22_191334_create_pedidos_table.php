<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePedidosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pedidos', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->bigInteger('producto_id')->unsigned();
            $table->bigInteger('estado_id')->unsigned();
            $table->bigInteger('vendedor_id')->unsigned();
            $table->bigInteger('cliente_id')->unsigned();

            $table->date('fecha');
            $table->integer('codigo');
            $table->float('valor');
            $table->float('direccion');
            $table->string('descuento');
            $table->float('total');

            $table->foreign('producto_id')->references('id')->on('productos')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            $table->foreign('estado_id')->references('id')->on('estados')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            $table->foreign('vendedor_id')->references('id')->on('users')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            $table->foreign('cliente_id')->references('id')->on('users')
                ->onDelete('cascade')
                ->onUpdate('cascade');

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
        Schema::dropIfExists('pedidos');
    }
}
