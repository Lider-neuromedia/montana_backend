<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToEncuestasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('encuestas', function (Blueprint $table) {
            $table->foreign('catalogo', 'fk_catalogos_encuestas')->references('id_catalogo')->on('catalogos')->onUpdate('NO ACTION')->onDelete('NO ACTION');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('encuestas', function (Blueprint $table) {
            $table->dropForeign('fk_catalogos_encuestas');
        });
    }
}
