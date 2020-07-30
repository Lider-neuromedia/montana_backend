<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

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
            $table->bigIncrements('id');

            $table->bigInteger('catalogo_id')->unsigned();
            $table->bigInteger('categoria_id')->unsigned();
            $table->bigInteger('descuento_id')->unsigned();
            $table->bigInteger('iva_id')->unsigned();
            $table->bigInteger('marca_id')->unsigned();

            $table->string('nombre');
            $table->integer('codigo');
            $table->string('referencia');
            $table->string('sku');
            $table->integer('stock');
            $table->float('precio');
            $table->float('precio_descuento');
            $table->string('descripcion_larga');
            $table->string('descripcion_corta');
            $table->string('imagen');
            $table->float('total');

            $table->foreign('catalogo_id')->references('id')->on('catalogos')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            $table->foreign('categoria_id')->references('id')->on('categorias')
                ->onDelete('cascade')
                ->onUpdate('cascade');
            
            $table->foreign('descuento_id')->references('id')->on('descuentos')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            $table->foreign('iva_id')->references('id')->on('ivas')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            $table->foreign('marca_id')->references('id')->on('marcas')
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
        Schema::dropIfExists('productos');
    }
}
