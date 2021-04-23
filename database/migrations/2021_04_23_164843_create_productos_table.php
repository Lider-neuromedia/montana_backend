<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('productos', function (Blueprint $table) {
            $table->increments('id_producto');
            $table->string('nombre', 150)->nullable();
            $table->text('codigo')->nullable();
            $table->text('referencia')->nullable();
            $table->bigInteger('stock')->nullable();
            $table->integer('precio')->nullable();
            $table->text('descripcion')->nullable();
            $table->text('sku')->nullable();
            $table->integer('total')->nullable();
            $table->unsignedInteger('descuento');
            $table->unsignedInteger('iva');
            $table->unsignedInteger('catalogo')->index('fk_catalogo');
            $table->unsignedInteger('marca')->index('fk_marca');
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
        Schema::dropIfExists('productos');
    }
}
