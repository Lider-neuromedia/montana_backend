<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToPreguntasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('preguntas', function (Blueprint $table) {
            $table->foreign('encuesta', 'campo_formulario')->references('id_form')->on('encuestas')->onUpdate('NO ACTION')->onDelete('NO ACTION');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('preguntas', function (Blueprint $table) {
            $table->dropForeign('campo_formulario');
        });
    }
}
