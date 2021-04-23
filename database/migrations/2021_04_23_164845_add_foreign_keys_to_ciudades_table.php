<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToCiudadesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ciudades', function (Blueprint $table) {
            $table->foreign('departamento', 'fi_departamento')->references('id_departamento')->on('departamentos')->onUpdate('NO ACTION')->onDelete('NO ACTION');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ciudades', function (Blueprint $table) {
            $table->dropForeign('fi_departamento');
        });
    }
}
