<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMultipleColumnsToTiendasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tiendas', function (Blueprint $table) {
            $table->string("sucursal")->nullable()->default("")->after('id_tiendas');
            $table->date("fecha_ingreso")->nullable()->after('telefono');
            $table->date("fecha_ultima_compra")->nullable()->after('fecha_ingreso');
            $table->double("cupo", 12, 2)->nullable()->default(0)->after("fecha_ultima_compra");
            $table->string("ciudad_codigo")->nullable()->default("")->after("cupo");
            $table->string("zona")->nullable()->default("")->after("ciudad_codigo");
            $table->string("bloqueado")->nullable()->default("")->after("zona");
            $table->date("bloqueado_fecha")->nullable()->after("bloqueado");
            $table->string("nombre_representante")->nullable()->default("")->after("bloqueado_fecha");
            $table->integer("plazo")->nullable()->default(0)->after("nombre_representante");
            $table->string("escala_factura")->nullable()->default("")->after("plazo");
            $table->text("observaciones")->nullable()->default("")->after("escala_factura");

            $table->string('nombre', 255)->nullable()->change();
            $table->string('lugar', 255)->nullable()->change();
            $table->string('local', 255)->nullable()->change();
            $table->string('direccion', 255)->nullable()->change();
            $table->string('telefono', 255)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tiendas', function (Blueprint $table) {
            $table->dropColumn("sucursal");
            $table->dropColumn("fecha_ingreso");
            $table->dropColumn("fecha_ultima_compra");
            $table->dropColumn("cupo");
            $table->dropColumn("ciudad_codigo");
            $table->dropColumn("zona");
            $table->dropColumn("bloqueado");
            $table->dropColumn("bloqueado_fecha");
            $table->dropColumn("nombre_representante");
            $table->dropColumn("plazo");
            $table->dropColumn("escala_factura");
            $table->dropColumn("observaciones");

            // $table->string('nombre', 45)->nullable()->change();
            // $table->string('lugar', 45)->nullable()->change();
            // $table->string('local', 20)->nullable()->change();
            // $table->string('direccion', 50)->nullable()->change();
            // $table->string('telefono', 20)->nullable()->change();
        });
    }
}
