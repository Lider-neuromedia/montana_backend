<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTiendasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tiendas', function (Blueprint $table) {
            $table->increments('id_tiendas');
            $table->string('nombre', 45)->nullable();
            $table->string('lugar', 45)->nullable();
            $table->string('local', 20)->nullable();
            $table->string('direccion', 50)->nullable();
            $table->string('telefono', 20)->nullable();
            $table->unsignedBigInteger('cliente')->default(0)->index('cliente_id_foreign');
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
        Schema::dropIfExists('tiendas');
    }
}
