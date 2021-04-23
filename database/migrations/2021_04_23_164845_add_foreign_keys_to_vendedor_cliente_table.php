<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToVendedorClienteTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('vendedor_cliente', function (Blueprint $table) {
            $table->foreign('cliente', 'fk_cliente_vendedor')->references('id')->on('users')->onUpdate('NO ACTION')->onDelete('NO ACTION');
            $table->foreign('vendedor', 'fk_vendedor_cliente')->references('id')->on('users')->onUpdate('NO ACTION')->onDelete('NO ACTION');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('vendedor_cliente', function (Blueprint $table) {
            $table->dropForeign('fk_cliente_vendedor');
            $table->dropForeign('fk_vendedor_cliente');
        });
    }
}
