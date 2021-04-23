<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToSeguimientoPqrsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('seguimiento_pqrs', function (Blueprint $table) {
            $table->foreign('pqrs', 'fk_seguimiento_pqrs')->references('id_pqrs')->on('pqrs')->onUpdate('NO ACTION')->onDelete('NO ACTION');
            $table->foreign('usuario', 'fk_seguimiento_usuario')->references('id')->on('users')->onUpdate('NO ACTION')->onDelete('NO ACTION');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('seguimiento_pqrs', function (Blueprint $table) {
            $table->dropForeign('fk_seguimiento_pqrs');
            $table->dropForeign('fk_seguimiento_usuario');
        });
    }
}
