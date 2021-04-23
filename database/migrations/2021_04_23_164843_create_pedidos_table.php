<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

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
            $table->integer('id_pedido', true);
            $table->date('fecha')->nullable();
            $table->text('codigo')->nullable();
            $table->string('metodo_pago', 100)->nullable();
            $table->bigInteger('sub_total')->nullable()->comment('Valor total sin descuento.');
            $table->bigInteger('total')->nullable()->comment('Valor aplicado con descuento o sin, en caso que  no lo tenga.');
            $table->unsignedInteger('descuento')->nullable()->comment('Descuento aplicar');
            $table->text('notas')->nullable();
            $table->unsignedBigInteger('vendedor')->default(0)->index('fk_pedido_vendedor');
            $table->unsignedBigInteger('cliente')->default(0)->index('fk_pedido_cliente');
            $table->unsignedInteger('estado')->index('fk_pedido_estado');
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
        Schema::dropIfExists('pedidos');
    }
}
