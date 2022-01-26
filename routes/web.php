<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
 */

Route::get('/', function () {
    return view('welcome');
});

if (env('COMMANDS', null) === true) {

    Route::get('/setup-storage', function () {
        $exitCode = Artisan::call('storage:link');
        \Log::info("Publicar Storage: $exitCode");
        return 'ok';
    });

    Route::get('/setup-db', function () {
        $exitCode = Artisan::call('migrate --seed --no-interaction --force');
        \Log::info("Migración Resultado: $exitCode");
        return 'Ok';
    });

}
