<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToPedidoProductosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pedido_productos', function (Blueprint $table) {
            $table->foreign('pedido', 'fk_pedido')->references('id_pedido')->on('pedidos')->onUpdate('NO ACTION')->onDelete('NO ACTION');
            $table->foreign('tienda', 'fk_pedido_prod')->references('id_tiendas')->on('tiendas')->onUpdate('NO ACTION')->onDelete('NO ACTION');
            $table->foreign('producto', 'fk_producto_ped')->references('id_producto')->on('productos')->onUpdate('NO ACTION')->onDelete('NO ACTION');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('pedido_productos', function (Blueprint $table) {
            $table->dropForeign('fk_pedido');
            $table->dropForeign('fk_pedido_prod');
            $table->dropForeign('fk_producto_ped');
        });
    }
}
