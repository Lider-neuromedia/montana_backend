<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEncuestaPreguntasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('encuesta_preguntas', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->bigInteger('encuesta_id')->unsigned();
            $table->bigInteger('pregunta_id')->unsigned();
            $table->bigInteger('user_id')->nullable()->unsigned();
            $table->bigInteger('valoracion_id')->nullable()->unsigned();

            $table->foreign('encuesta_id')->references('id')->on('encuestas')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            $table->foreign('pregunta_id')->references('id')->on('preguntas')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            $table->foreign('user_id')->references('id')->on('users')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            $table->foreign('valoracion_id')->references('id')->on('valoracions')
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
        Schema::dropIfExists('encuesta_preguntas');
    }
}
