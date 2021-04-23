<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGaleriaProductosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('galeria_productos', function (Blueprint $table) {
            $table->increments('id_galeria_prod');
            $table->text('image')->nullable();
            $table->string('name_img', 250)->nullable();
            $table->integer('destacada')->nullable();
            $table->unsignedInteger('producto')->index('fk_producto');
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
        Schema::dropIfExists('galeria_productos');
    }
}
