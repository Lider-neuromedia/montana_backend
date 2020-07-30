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


//Route::get('administradores', 'AdministradorController');

Route::get('/user-rol/{id}','UserController@getForRole');
Route::get('/vendedores','UserController@getVendedores');
Route::get('/vendedor/{id}','UserController@getVendedor');
Route::get('/clientes','UserController@getClientes');
Route::get('/cliente/{id}','UserController@getCliente');