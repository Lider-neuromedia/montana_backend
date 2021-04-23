<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePedidoProductosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pedido_productos', function (Blueprint $table) {
            $table->integer('id_pedido_prod', true);
            $table->integer('pedido')->default(0)->index('fk_pedido');
            $table->unsignedInteger('producto')->default(0)->index('fk_producto_ped');
            $table->integer('cantidad_producto')->nullable();
            $table->unsignedInteger('tienda')->nullable()->index('fk_pedido_prod');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pedido_productos');
    }
}
