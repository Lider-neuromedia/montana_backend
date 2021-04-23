<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToPedidosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pedidos', function (Blueprint $table) {
            $table->foreign('cliente', 'fk_pedido_cliente')->references('id')->on('users')->onUpdate('NO ACTION')->onDelete('NO ACTION');
            $table->foreign('estado', 'fk_pedido_estado')->references('id_estado')->on('estados')->onUpdate('NO ACTION')->onDelete('NO ACTION');
            $table->foreign('vendedor', 'fk_pedido_vendedor')->references('id')->on('users')->onUpdate('NO ACTION')->onDelete('NO ACTION');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('pedidos', function (Blueprint $table) {
            $table->dropForeign('fk_pedido_cliente');
            $table->dropForeign('fk_pedido_estado');
            $table->dropForeign('fk_pedido_vendedor');
        });
    }
}
