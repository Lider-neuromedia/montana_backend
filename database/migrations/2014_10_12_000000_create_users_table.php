<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('rol_id');
            $table->string('name');
            $table->string('apellidos');
            $table->string('email')->unique();
            $table->enum('tipo_identificacion', ['Cedula', 'Cedula extrangeria', 'Pasaporte'])->nullable()->default('Cedula');
            $table->string('dni');
            $table->string('password');
            $table->timestamp('email_verified_at')->nullable();
            $table->foreign('rol_id')->references('id')->on('roles')->onDelete('cascade')->onUpdate('cascade');
            $table->rememberToken();
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
        Schema::dropIfExists('users');
    }
}
