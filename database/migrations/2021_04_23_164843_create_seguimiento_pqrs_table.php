<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSeguimientoPqrsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('seguimiento_pqrs', function (Blueprint $table) {
            $table->bigInteger('id_seguimiento', true);
            $table->unsignedBigInteger('usuario')->index('fk_seguimiento_usuario');
            $table->unsignedBigInteger('pqrs')->index('fk_seguimiento_pqrs');
            $table->text('mensaje');
            $table->time('hora');
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
        Schema::dropIfExists('seguimiento_pqrs');
    }
}
