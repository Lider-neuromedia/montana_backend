<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVendedorClienteTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vendedor_cliente', function (Blueprint $table) {
            $table->increments('id_vendedor_cliente');
            $table->unsignedBigInteger('vendedor')->default(0)->index('fk_vendedor_cliente');
            $table->unsignedBigInteger('cliente')->default(0)->index('fk_cliente_vendedor');
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
        Schema::dropIfExists('vendedor_cliente');
    }
}
