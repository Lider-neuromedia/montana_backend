<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToAmpliacionCupoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ampliacion_cupo', function (Blueprint $table) {
            $table->foreign('cliente', 'fk_ampliacion_cliente')->references('id')->on('users')->onUpdate('NO ACTION')->onDelete('NO ACTION');
            $table->foreign('vendedor', 'fk_ampliacion_vendedor')->references('id')->on('users')->onUpdate('NO ACTION')->onDelete('NO ACTION');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ampliacion_cupo', function (Blueprint $table) {
            $table->dropForeign('fk_ampliacion_cliente');
            $table->dropForeign('fk_ampliacion_vendedor');
        });
    }
}
